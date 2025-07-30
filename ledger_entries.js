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
    let row;
    try {
        test.type = "date";
        row = document.getElementsByClassName("date-fallback");
        for (let i = 0; i < row.length; i++) {
            if (row[i].style.display === "none" && row[i].tagName === "SELECT") {
                row[i].value = "";
            }
        }
    } catch (e) {
        row = document.getElementsByClassName("date-fallback");
        for (let i = 0; i < row.length; i++) {
            if (row[i].style.display === "none") {
                row[i].style.removeProperty("display");
            } else {
                if (row[i].tagName === "INPUT") {
                    row[i].value = "";
                    row[i].removeAttribute("required");
                }
                row[i].style.display = "none";
            }
        }
        elementId += "AA";
    }
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            const el = document.getElementById(elementId);
            if (el) {
                el.focus();
                el.scrollIntoView({ behavior: "instant", block: "end", inline: "end" });
            }
        }, 1);
    });
}
