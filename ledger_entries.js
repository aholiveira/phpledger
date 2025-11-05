function update_date(id) {
    document.getElementById(id).value =
        document.getElementById(id + 'AA').value +
        document.getElementById(id + 'MM').value.padStart(2, '0') +
        document.getElementById(id + 'DD').value.padStart(2, '0');
}

function clear_filter() {
    const now = new Date();
    document.getElementById("filter_entry_type").value = "";
    document.getElementById("filter_account_id").value = "";
    document.getElementById("filter_sdate").value = now.getFullYear().toString() + "-" + (now.getMonth() + 1).toString().padStart(2, "0") + "-01";
    document.getElementById("filter_edate").value = now.getFullYear().toString() + "-" + (now.getMonth() + 1).toString().padStart(2, "0") + "-" + now.getDate().toString().padStart(2, "0");
    document.getElementsByName("datefilter")[0].submit();
}

function add_filter(filter_name, filter_value) {
    document.getElementById("filter_" + filter_name).value = filter_value;
}

function toggleDateElements(elementId) {
    const test = document.createElement("input");
    try {
        test.type = "date";
        let rows = document.getElementsByClassName("date-fallback");
        for (let row of rows) {
            if (row.style.display === "none" && row.tagName === "SELECT") {
                row.value = "";
            }
        }
    } catch (e) {
        let rows = document.getElementsByClassName("date-fallback");
        for (let row of rows) {
            if (row.style.display === "none") {
                row.style.removeProperty("display");
            } else {
                if (row.tagName === "INPUT") {
                    row.value = "";
                    row.removeAttribute("required");
                }
                row.style.display = "none";
            }
        }
        elementId += "AA";
    }
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            const el = document.getElementById(elementId);
            if (el) {
                el.focus();
                el.closest("tr").scrollIntoView({ behavior: "auto", block: "nearest" });
            }
        }, 1);
    });
}
