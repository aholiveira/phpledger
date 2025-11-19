<?php
include_once "common.php";
use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\MySql\MySqlObject;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Logger;
use PHPLedger\Views\ViewFactory;

debug_print("Running tests...");
$logger = new Logger("run.log");
$data_storage = ObjectFactory::dataStorage();
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
$object = ObjectFactory::account();
run_tests($object);
$balance = $object->getBalanceOnDate(new DateTime());
assert(is_float($balance['income']));
assert(is_float($balance['expense']));
assert(is_float($balance['balance']));
$viewer = ViewFactory::instance()->accountBalanceView($object);
/*debug_print($viewer->printObjectList($object->getList(array('activa' => array('operator' => '=', 'value' => '1')))));
 */
$viewer = ViewFactory::instance()->accountView($object);
run_views($viewer, $object);
$object = ObjectFactory::accounttype();
run_tests($object);
$viewer = ViewFactory::instance()->accountTypeView($object);
run_views($viewer, $object);

$object = ObjectFactory::currency();
run_tests($object, 1);

$object = ObjectFactory::ledger();
run_tests($object);
/*$object = ObjectFactory::ledgerentry();
run_tests($object);
$viewer = ViewFactory::instance()->ledgerEntryView($object);
run_views($viewer, $object);
$object = ObjectFactory::entryCategory();
$logger->dump($object->getList());
/*run_tests($object);
debug_print("OBJECT 145");
$object->getById(0);
$logger->dump($object);
exit(0);
/*
$viewer = ViewFactory::instance()->entryCategoryView($object);
print $viewer->printForm();
$object->getById(7);
$viewer = ViewFactory::instance()->entryCategoryView($object);
run_views($viewer, $object);
print $viewer->printForm();

debug_print("YEAR REPORT:");
$object = ObjectFactory::reportMonth();
$object->getReport(array("year" => 2021));
$viewer = ViewFactory::instance()->reportMonthHtmlView($object);
debug_print($viewer->printAsTable());
/*
$object = ObjectFactory::reportYear();
$object->getReport(array("year" => 2021));
$logger->dump($object);
$viewer = ViewFactory::instance()->reportYearHtmlView($object);
//debug_print($viewer->printAsTable());
*/
function run_tests(MySqlObject $object, $id = 1)
{
    try {
        debug_print("OBJECT: {$object}");
        debug_print("getById");
        $object = $object->getById($id);
        if (isset($object->id)) {
            assert($object->id === $id);
        }
        assert($object->update() === true, "save#{$object}#");
        debug_print("getList#{$object}#");
        $fieldFilter = [];
        if ($object instanceof ledgerentry) {
            debug_print("LEDGER ENTRY FILTER");
            $fieldFilter = ['entry_date' => ['operator' => 'BETWEEN', 'value' => ['2022-01-01', '2022-01-02']]];
        }
        $object->getList($fieldFilter);
        debug_print("getNextId#{$object}#");
        assert($object->getNextId() >= 0);
    } catch (Exception $ex) {
        debug_print($ex->getMessage());
    }
}

function run_views(ObjectViewer $viewer, DataObjectInterface $object)
{
    try {
        debug_print("OBJECT: " . get_class($viewer));
        debug_print($viewer->printObject());
        $fieldFilter = [];
        if ($object instanceof ledgerentry) {
            debug_print("LEDGER ENTRY FILTER");
            $fieldFilter = ['entry_date' => ['operator' => 'BETWEEN', 'value' => ['2022-01-01', '2022-01-02']]];
        }
        debug_print($viewer->printObjectList($object->getList($fieldFilter)));
    } catch (Exception $ex) {
        debug_print($ex->getMessage());
    }
}
