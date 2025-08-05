function updateRowColors(tableId) {
    const rows = document.querySelectorAll(`#${tableId} tbody tr`);
    let visibleIndex = 0;
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            row.classList.remove('even', 'odd');
            row.classList.add(visibleIndex % 2 === 0 ? 'even' : 'odd');
            visibleIndex++;
        }
    });
}
