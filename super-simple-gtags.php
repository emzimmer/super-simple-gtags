<?php
/*
Plugin Name: Super Simple GTags
Description: The simplest way to add Google tags (GA4, GTM, Ads) to your WordPress site. No bloat, no complexity - just tag configs.
Version: 1.0.0
Author: Max Zimmer
Author URI: https://emzimmer.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: super-simple-gtags
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) exit;

/**
 * Main plugin class for Super Simple GTags
 * 
 * Handles the core functionality of adding Google tags to WordPress sites
 * including GA4 and Google Ads tracking codes.
 * 
 * @since 1.0.0
 */
class SuperSimpleGTags {
	/** @var SuperSimpleGTags|null Singleton instance */
	private static $instance = null;
	
	/** @var string Plugin directory path */
	private $plugin_path;
	
	/** @var string Plugin directory URL */
	private $plugin_url;

	/**
	 * Gets the singleton instance of the plugin
	 * 
	 * @since 1.0.0
	 * @return SuperSimpleGTags Plugin instance
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - Sets up plugin paths and hooks
	 * 
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->plugin_url = plugin_dir_url(__FILE__);

		// Register all WordPress hooks
		add_action('admin_menu', [$this, 'add_settings_page']);
		add_action('admin_init', [$this, 'register_settings']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
		add_action('wp_head', [$this, 'add_tags'], 1);
	}

	/**
	 * Adds the plugin settings page to WordPress admin menu
	 * 
	 * @since 1.0.0
	 */
	public function add_settings_page() {
		add_options_page(
			__('Super Simple GTags Settings', 'super-simple-gtags'),
			__('GTags Settings', 'super-simple-gtags'),
			'manage_options',
			'super-simple-gtags',
			[$this, 'render_settings_page']
		);
	}

	/**
	 * Registers plugin settings in WordPress
	 * 
	 * @since 1.0.0
	 */
	public function register_settings() {
		// Register tags array setting
		register_setting('sstg_settings', 'sstg_tags', [
			'type' => 'array',
			'default' => []
		]);
		// Register debug mode setting
		register_setting('sstg_settings', 'sstg_debug_mode');
	}

	/**
	 * Enqueues admin scripts for the plugin settings page
	 * 
	 * @since 1.0.0
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_admin_scripts($hook) {
		// Only load on plugin settings page
		if ('settings_page_super-simple-gtags' !== $hook) {
			return;
		}
		wp_enqueue_script(
			'sstg-admin',
			$this->plugin_url . 'admin.js',
			[],
			'1.0.0',
			true
		);
	}

	/**
	 * Renders the plugin settings page HTML
	 * 
	 * @since 1.0.0
	 */
	public function render_settings_page() {
		// Get saved tags or set default if empty
		$tags = get_option('sstg_tags', []);
		if (empty($tags)) {
			$tags = [['id' => '', 'type' => 'ga4']];
		}
		?>
		<div class="wrap">
			<h1><?php _e('Super Simple GTags Settings', 'super-simple-gtags'); ?></h1>
			<p class="description">The simplest way to add Google tags to your website. Just adding the base gtag script and configuration properties. No bloat.</p>
			<p>
				<strong><?php _e('Do you like this plugin?', 'super-simple-gtags'); ?></strong> 
				<a href="https://buymeacoffee.com/emzimmer" target="_blank"><?php _e('Buy me a coffee!', 'super-simple-gtags'); ?></a> 
				😊 
				<?php _e('Or,', 'super-simple-gtags'); ?> 
				<a href="https://paypal.me/emaxzimmer" target="_blank"><?php _e('Contribute by PayPal.', 'super-simple-gtags'); ?></a> 
			</p>
			<form method="post" action="options.php">
				<?php
				settings_fields('sstg_settings');
				do_settings_sections('sstg_settings');
				?>
				<style>
					#sstg-tags-table {
						max-width: fit-content;
					}

					#sstg-tags-table input {
						min-width: 350px;
					}

					.remove-tag {
						color: #d63638 !important;
						border-color: #d63638 !important;
					}

					#add-tag {
						margin-top: 20px;
						margin-bottom: 20px;
					}
				</style>

				<table class="widefat" id="sstg-tags-table">
					<thead>
						<tr>
							<th><?php _e('Measurement ID', 'super-simple-gtags'); ?></th>
							<th><?php _e('Action', 'super-simple-gtags'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($tags as $index => $tag) : ?>
						<tr class="tag-row">
							<td>
								<input type="text" 
									name="sstg_tags[<?php echo $index; ?>][id]" 
									value="<?php echo esc_attr($tag['id']); ?>"
									placeholder="G-XXXXXXXXXX, GTM-XXXXXX, or AW-XXXXXX"
								/>
							</td>
							<td>
								<button type="button" class="button remove-tag"><?php _e('Remove', 'super-simple-gtags'); ?></button>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<button type="button" class="button" id="add-tag"><?php _e('Add Another Tag', 'super-simple-gtags'); ?></button>
				
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Debug Mode', 'super-simple-gtags'); ?></th>
						<td>
							<label>
								<input type="checkbox" 
									name="sstg_debug_mode" 
									value="1" 
									<?php checked(1, get_option('sstg_debug_mode'), true); ?>
								/>
								<?php _e('Enable debug mode', 'super-simple-gtags'); ?>
							</label>
							<p class="description"><?php _e('Enables debug mode for developers and staging sites.', 'super-simple-gtags'); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Adds Google tags to the site header
	 * 
	 * @since 1.0.0
	 */
	public function add_tags() {
		$tags = get_option('sstg_tags', []);
		$debug_mode = get_option('sstg_debug_mode');

		// Only proceed if we have tags configured
		if (!empty($tags)) {
			// Filter out empty tag IDs
			$valid_tags = array_filter($tags, function($tag) {
				return !empty($tag['id']);
			});

			if (!empty($valid_tags)) {
				$this->render_gtag_script($valid_tags, $debug_mode);
			}
		}
	}

	/**
	 * Renders the Google Tag Manager script with configured tags
	 * 
	 * @since 1.0.0
	 * @param array $tags Array of configured tags
	 * @param bool $debug_mode Whether debug mode is enabled
	 */
	private function render_gtag_script($tags, $debug_mode) {
		?>
		<!-- Google tag (gtag.js) -->
		<script async src="https://www.googletagmanager.com/gtag/js"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			<?php 
			foreach ($tags as $tag) {
				if ($debug_mode) {
					echo "gtag('config', '" . esc_js($tag['id']) . "', {'debug_mode': true});\n";
				} else {
					echo "gtag('config', '" . esc_js($tag['id']) . "');\n";
				}
			}
			?>
		</script>
		<?php
	}	
}

// Initialize the plugin singleton
SuperSimpleGTags::get_instance();
