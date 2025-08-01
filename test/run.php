<?php
include "common.php";

debug_print("Running tests...");
$logger = new Logger("run.log");
$data_storage = $object_factory->data_storage();
if (!$data_storage->check()) {
    print "DB NEEDS UPDATE...";
    print $data_storage->message();
    if ($data_storage->update()) {
        print $data_storage->message();
        print "UPDATE SUCESS<br>\n";
    } else {
        print "UPDATE FAILED<br>\n";
        exit(0);
    }
}

#$data_storage->populateRandomData();
$object = $object_factory->account();
run_tests($object);
$balance = $object->getBalanceOnDate(new DateTime());
assert(is_float($balance['income']));
assert(is_float($balance['expense']));
assert(is_float($balance['balance']));
$viewer = $view_factory->account_balance_view($object);
/*debug_print($viewer->printObjectList($object->getList(array('activa' => array('operator' => '=', 'value' => '1')))));
 */
$viewer = $view_factory->account_view($object);
run_views($viewer, $object);
$object = $object_factory->accounttype();
run_tests($object);
$viewer = $view_factory->account_type_view($object);
run_views($viewer, $object);

$object = $object_factory->currency();
run_tests($object, 1);

$object = $object_factory->ledger();
run_tests($object);
/*$object = $object_factory->ledgerentry();
run_tests($object);
$viewer = $view_factory->ledger_entry_view($object);
run_views($viewer, $object);
$object = $object_factory->entry_category();
$logger->dump($object->getList());
/*run_tests($object);
debug_print("OBJECT 145");
$object->getById(0);
$logger->dump($object);
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
$logger->dump($object);
$viewer = $view_factory->report_year_view($object);
//debug_print($viewer->printAsTable());
*/
function run_tests(mysql_object $object, $id = 1)
{
    global $logger;
    try {
        debug_print("OBJECT: {$object}");
        debug_print("getById");
        $object = $object->getById($id);
        if (isset($object->id)) {
            assert($object->id === $id);
        }
        assert($object->update() === true, "save#{$object}#");
        debug_print("getList#{$object}#");
        $field_filter = [];
        if ($object instanceof ledgerentry) {
            debug_print("LEDGER ENTRY FILTER");
            $field_filter = ['entry_date' => ['operator' => 'BETWEEN', 'value' => "'2022-01-01' AND '2022-01-02'"]];
        }
        $object->getList($field_filter);
        debug_print("getNextId#{$object}#");
        assert($object->getNextId() >= 0);
    } catch (Exception $ex) {
        debug_print($ex->getMessage());
    }
}

function run_views(object_viewer $viewer, iobject $object)
{
    try {
        debug_print("OBJECT: " . get_class($viewer));
        debug_print($viewer->printObject());
        $field_filter = [];
        if ($object instanceof ledgerentry) {
            debug_print("LEDGER ENTRY FILTER");
            $field_filter = ['entry_date' => ['operator' => 'BETWEEN', 'value' => "'2022-01-01' AND '2022-01-02'"]];
        }
        debug_print($viewer->printObjectList($object->getList($field_filter)));
    } catch (Exception $ex) {
        debug_print($ex->getMessage());
    }
}
