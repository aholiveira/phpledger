/*!
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */
function updateRowColors(tableId) {
    const rows = document.querySelectorAll(`#${tableId} tbody tr`);
    let visibleIndex = 0;
    for (const row of rows) {
        if (row.style.display !== 'none') {
            row.classList.remove('even', 'odd');
            row.classList.add(visibleIndex % 2 === 0 ? 'even' : 'odd');
            visibleIndex++;
        }
    };
}
