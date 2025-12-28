function update_date(id) {
    document.getElementById(id).value =
        document.getElementById(id + 'AA').value +
        document.getElementById(id + 'MM').value.padStart(2, '0') +
        document.getElementById(id + 'DD').value.padStart(2, '0');
}

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

function toggleDateElements(elementId) {
    const input = document.createElement("input");
    input.type = "date";
    const supportsDate = input.type === "date";
    const rows = document.getElementsByClassName("date-fallback");

    for (const row of rows) {
        if (supportsDate) {
            if (row.tagName === "SELECT" && row.style.display === "none") {
                row.value = "";
            }
            continue;
        }
        const isHidden = row.style.display === "none";
        row.style.display = isHidden ? "" : "none";
        if (!isHidden && row.tagName === "INPUT") {
            row.value = "";
            row.removeAttribute("required");
        }
    }

    const focusTarget = () => {
        const suffix = supportsDate ? "" : "AA";
        const el = document.getElementById(elementId + suffix);
        if (!el) { return; }
        setTimeout(() => {
            el.focus();
            el.closest("tr")?.scrollIntoView({ behavior: "auto", block: "nearest" });
        }, 1);
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", focusTarget, { once: true });
    }
    else {
        focusTarget();
    }
}
