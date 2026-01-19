/*!
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */
function clear_filter() {
    const now = new Date();
    document.getElementById("filter_entryType").value = "";
    document.getElementById("filter_accountId").value = "";
    document.getElementById("filter_startDate").value = now.getFullYear().toString() + "-" + (now.getMonth() + 1).toString().padStart(2, "0") + "-01";
    document.getElementById("filter_endDate").value = now.getFullYear().toString() + "-" + (now.getMonth() + 1).toString().padStart(2, "0") + "-" + now.getDate().toString().padStart(2, "0");
    document.getElementById("datefilter").submit();
}

function add_filter(filter_name, filter_value) {
    document.getElementById("filter_" + filter_name).value = filter_value;
}

document.addEventListener("DOMContentLoaded", () => {
    const currencyAmount = document.querySelector('input[name="currencyAmount"]');
    const euroAmount = document.querySelector('input[name="euroAmount"]');
    const exchangeRate = document.querySelector('input[name="exchangeRate"]');
    const direction = document.querySelector('select[name="direction"]');

    function recalc(source) {
        const curr = Number.parseFloat(currencyAmount.value) || 0;
        const euro = Number.parseFloat(euroAmount.value) || 0;
        const rate = Number.parseFloat(exchangeRate.value) || 0;
        const dir = Number.parseFloat(direction.value) || 0;

        if (source === "currency") {
            euroAmount.value = (dir * curr * rate).toFixed(2);
        } else if (source === "euro") {
            exchangeRate.value = curr ? (dir * euro / curr).toFixed(8) : rate.toFixed(8);
        } else if (source === "rate") {
            euroAmount.value = (dir * curr * rate).toFixed(2);
        }
    }

    currencyAmount.addEventListener("input", () => recalc("currency"));
    euroAmount.addEventListener("input", () => recalc("euro"));
    exchangeRate.addEventListener("input", () => recalc("rate"));
});

function focusTarget(elementId) {
    const el = document.getElementById(elementId);
    if (!el) { return; }
    setTimeout(() => {
        el.focus();
        el.closest("tr")?.scrollIntoView({ behavior: "auto", block: "nearest" });
    }, 1);
}
