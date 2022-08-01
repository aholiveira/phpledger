<?php
include "common.php";
debug_print("Running tests...");
$checkdb = new check_db($config);
if (!$checkdb->check()) {
    print "DB NEEDS UPDATE...";
    print $checkdb->message;
    if ($checkdb->update()) {
        print $checkdb->message;
        print "DONE UPDATE<br>\n";
    } else {
        print "UPATE FAILED<br>\n";
        exit(0);
    }
}

$checkdb->populateRandomData();
exit(0);
/*$object1 = $object_factory->account();
run_tests($object);
$balance = $object->getBalanceOnDate(new DateTime());
assert(($balance['income'] - 93764.74) <= 0);
assert(($balance['expense'] - 93764.74) <= 0);
assert($balance['balance'] == 0);
$viewer = $view_factory->account_balance_view($object);
debug_print($viewer->printObjectList($object->getAll(array('activa' => array('operator' => '=', 'value' => '1')))));

$viewer = $view_factory->account_view($object);
run_views($viewer, $object);
$object = $object_factory->accounttype();
run_tests($object);
$viewer = $view_factory->account_type_view($object);
run_views($viewer, $object);

$object = $object_factory->currency();
run_tests($object, "EUR");*/
$object = $object_factory->ledger();
run_tests($object);
/*$object = $object_factory->ledgerentry();
run_tests($object);
$viewer = $view_factory->ledger_entry_view($object);
run_views($viewer, $object);
$object = $object_factory->entry_category();
print_var($object->getAll());
/*run_tests($object);
debug_print("OBJECT 145");
$object->getById(0);
print_var($object);
exit(0);
/*
$viewer = $view_factory->entry_category_view($object);
print $viewer->printForm();
$object->getById(7);
$viewer = $view_factory->entry_category_view($object);
run_views($viewer, $object);
print $viewer->printForm();

debug_print("YEAR REPORT:");
$object = $object_factory->report_month();
$object->getReport(array("year" => 2021));
$viewer = $view_factory->report_month_view($object);
debug_print($viewer->printAsTable());
/*
$object = $object_factory->report_year();
$object->getReport(array("year" => 2021));
print_var($object);
$viewer = $view_factory->report_year_view($object);
//debug_print($viewer->printAsTable());
*/
function run_tests(mysql_object $object, $id = 1)
{
    try {
        debug_print("OBJECT: {$object}");
        debug_print("getById");
        $object->getById($id);
        if (isset($object->id)) {
            assert($object->id === $id);
            //print_var($object);
        }
        assert($object->save() === true, "save#{$object}#");
        debug_print("getAll#{$object}#");
        $field_filter = array();
        if ($object instanceof ledgerentry) {
            debug_print("LEDGER ENTRY FILTER");
            $field_filter = array('data_mov' => array('operator' => 'BETWEEN', 'value' => "'2022-01-01' AND '2022-01-02'"));
        }
        $object->getAll($field_filter);
        debug_print("getFreeId#{$object}#");
        assert($object->getFreeId() >= 0);
    } catch (Exception $ex) {
        debug_print($ex->getMessage());
    }
}

function run_views(object_viewer $viewer, iobject $object)
{
    try {
        debug_print("OBJECT: " . get_class($viewer));
        debug_print($viewer->printObject());
        $field_filter = array();
        if ($object instanceof ledgerentry) {
            debug_print("LEDGER ENTRY FILTER");
            $field_filter = array('data_mov' => array('operator' => 'BETWEEN', 'value' => "'2022-01-01' AND '2022-01-02'"));
        }
        debug_print($viewer->printObjectList($object->getAll($field_filter)));
    } catch (Exception $ex) {
        debug_print($ex->getMessage());
    }
}
