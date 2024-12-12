document.addEventListener('DOMContentLoaded', function() {
	const tagsTable = document.getElementById('sstg-tags-table');
	const addButton = document.getElementById('add-tag');

	// Add new tag row
	addButton.addEventListener('click', function() {
		const tbody = tagsTable.querySelector('tbody');
		const newRow = document.createElement('tr');
		newRow.className = 'tag-row';
		
		const rowCount = tbody.querySelectorAll('tr').length;
		
		newRow.innerHTML = `
			<td>
				<input type="text" 
					name="sstg_tags[${rowCount}][id]" 
					placeholder="G-XXXXXXXXXX, GTM-XXXXXX, or AW-XXXXXX"
				/>
			</td>
			<td>
				<button type="button" class="button remove-tag">Remove</button>
			</td>
		`;
		
		tbody.appendChild(newRow);
	});

	// Remove tag row
	tagsTable.addEventListener('click', function(e) {
		if (e.target.classList.contains('remove-tag')) {
			const row = e.target.closest('tr');
			row.remove();
			reindexRows();
		}
	});

	// Reindex rows to keep array indexes sequential
	function reindexRows() {
		const rows = tagsTable.querySelectorAll('tbody tr');
		rows.forEach((row, index) => {
			const input = row.querySelector('input');
			input.name = `sstg_tags[${index}][id]`;
		});
	}
});
