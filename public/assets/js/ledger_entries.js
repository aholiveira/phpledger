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
