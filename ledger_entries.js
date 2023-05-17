function update_date(id) {
    document.getElementById(id).value =
        document.getElementById(id + 'AA').value +
        document.getElementById(id + 'MM').value +
        document.getElementById(id + 'DD').value;
}

function clear_filter() {
    document.getElementById("filter_entry_type").value = "";
    document.getElementById("filter_conta_id").value = "";
    document.getElementById("filter_sdate").value = (new Date).getFullYear().toString() + "-" + ((new Date).getMonth() + 1).toString().padStart(2, "0") + "-01";
    document.getElementById("filter_edate").value = (new Date).getFullYear().toString() + "-" + ((new Date).getMonth() + 1).toString().padStart(2, "0") + "-" + (new Date).getDate().toString().padStart(2, "0");
    document.getElementsByName("datefilter")[0].submit();
}

function add_filter(filter_name, filter_value) {
    document.getElementById("filter_" + filter_name).value = filter_value;
}

function toggleDateElements(elementId) {
    var test = document.createElement("input");
    try {
        test.type = "date";
        row = document.getElementsByClassName("date-fallback");
        for (i = 0; i < row.length; i++) {
            if (row[i].style.display == "none" && row[i].tagName == "SELECT") {
                row[i].value = "";
            }
        }
    } catch (e) {
        row = document.getElementsByClassName("date-fallback");
        for (i = 0; i < row.length; i++) {
            if (row[i].style.display == "none") {
                row[i].style.removeProperty("display");
            } else {
                if (row[i].tagName == "INPUT") {
                    row[i].value = "";
                    row[i].removeAttribute("required");
                }
                row[i].style.display = "none";
            }
        }
        elementId = elementId + "AA";
    }
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            document.getElementById(elementId).focus();
            document.getElementById(elementId).scrollIntoView({
                behavior: "instant",
                block: "end",
                inline: "end"
            });
        }, 1)
    });
}
