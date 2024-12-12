document.addEventListener('DOMContentLoaded', function() {
	let rowCount = document.querySelectorAll('.tag-row').length;

	document.getElementById('add-tag').addEventListener('click', function() {
		const newRow = `
			<tr class="tag-row">
				<td>
					<select name="sstg_tags[${rowCount}][type]">
						<option value="ga4">GA4</option>
						<option value="ads">Google Ads</option>
					</select>
				</td>
				<td>
					<input type="text" 
						name="sstg_tags[${rowCount}][id]" 
						placeholder="G-XXXXXXXXXX or AW-XXXXXX"
					/>
				</td>
				<td>
					<button type="button" class="button remove-tag">Remove</button>
				</td>
			</tr>
		`;
		document.querySelector('#sstg-tags-table tbody').insertAdjacentHTML('beforeend', newRow);
		rowCount++;
	});

	document.addEventListener('click', function(e) {
		if (e.target.classList.contains('remove-tag')) {
			const rows = document.querySelectorAll('.tag-row');
			if (rows.length > 1) {
				e.target.closest('tr').remove();
			} else {
				e.target.closest('tr').querySelector('input').value = '';
			}
		}
	});
});
