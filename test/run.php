<?php
include_once "common.php";

debug_print("Running tests...");
$logger = new Logger("run.log");
$data_storage = $objectFactory->dataStorage();
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
$object = $objectFactory->account();
run_tests($object);
$balance = $object->getBalanceOnDate(new DateTime());
assert(is_float($balance['income']));
assert(is_float($balance['expense']));
assert(is_float($balance['balance']));
$viewer = $viewFactory->account_balance_view($object);
/*debug_print($viewer->printObjectList($object->getList(array('activa' => array('operator' => '=', 'value' => '1')))));
 */
$viewer = $viewFactory->account_view($object);
run_views($viewer, $object);
$object = $objectFactory->accounttype();
run_tests($object);
$viewer = $viewFactory->account_type_view($object);
run_views($viewer, $object);

$object = $objectFactory->currency();
run_tests($object, 1);

$object = $objectFactory->ledger();
run_tests($object);
/*$object = $objectFactory->ledgerentry();
run_tests($object);
$viewer = $viewFactory->ledger_entry_view($object);
run_views($viewer, $object);
$object = $objectFactory->entryCategory();
$logger->dump($object->getList());
/*run_tests($object);
debug_print("OBJECT 145");
$object->getById(0);
$logger->dump($object);
exit(0);
/*
$viewer = $viewFactory->entry_category_view($object);
print $viewer->printForm();
$object->getById(7);
$viewer = $viewFactory->entry_category_view($object);
run_views($viewer, $object);
print $viewer->printForm();

debug_print("YEAR REPORT:");
$object = $objectFactory->reportMonth();
$object->getReport(array("year" => 2021));
$viewer = $viewFactory->report_month_view($object);
debug_print($viewer->printAsTable());
/*
$object = $objectFactory->reportYear();
$object->getReport(array("year" => 2021));
$logger->dump($object);
$viewer = $viewFactory->report_year_view($object);
//debug_print($viewer->printAsTable());
*/
function run_tests(MySqlObject $object, $id = 1)
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

function run_views(ObjectViewer $viewer, iObject $object)
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
