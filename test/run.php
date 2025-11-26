<?php
include_once "common.php";
use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\MySql\MySqlObject;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Logger;
use PHPLedger\Views\ViewFactory;
use PHPLedger\Views\ObjectViewer;

debugPrint("Running tests...");
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
runTests($object);
$balance = $object->getBalanceOnDate(new DateTime());
assert(is_float($balance['income']));
assert(is_float($balance['expense']));
assert(is_float($balance['balance']));
$viewer = ViewFactory::instance()->accountBalanceView($object);
/*debugPrint($viewer->printObjectList($object->getList(array('activa' => array('operator' => '=', 'value' => '1')))));
 */
$viewer = ViewFactory::instance()->accountView($object);
runViews($viewer, $object);
$object = ObjectFactory::accounttype();
runTests($object);
$viewer = ViewFactory::instance()->accountTypeView($object);
runViews($viewer, $object);

$object = ObjectFactory::currency();
runTests($object, 1);

$object = ObjectFactory::ledger();
runTests($object);
$runAdditional = false;
if ($runAdditional) {
    $object = ObjectFactory::ledgerentry();
    runTests($object);
    $viewer = ViewFactory::instance()->ledgerEntryView($object);
    runViews($viewer, $object);
    $object = ObjectFactory::entryCategory();
    $logger->dump($object->getList());
    runTests($object);
    debugPrint("OBJECT 145");
    $object->getById(0);
    $logger->dump($object);
    $viewer = ViewFactory::instance()->entryCategoryView($object);
    print $viewer->printForm();
    $object->getById(7);
    $viewer = ViewFactory::instance()->entryCategoryView($object);
    runViews($viewer, $object);
    print $viewer->printForm();
    debugPrint("YEAR REPORT:");
    $object = ObjectFactory::reportMonth();
    $object->getReport(array("year" => 2021));
    $viewer = ViewFactory::instance()->reportMonthHtmlView($object);
    debugPrint($viewer->printAsTable());
    $object = ObjectFactory::reportYear();
    $object->getReport(array("year" => 2021));
    $logger->dump($object);
    $viewer = ViewFactory::instance()->reportYearHtmlView($object);
}
function runTests(MySqlObject $object, $id = 1)
{
    try {
        debugPrint("OBJECT: {$object}");
        debugPrint("getById");
        $object = $object->getById($id);
        if (isset($object->id)) {
            assert($object->id === $id);
        }
        assert($object->update() === true, "save#{$object}#");
        debugPrint("getList#{$object}#");
        $fieldFilter = [];
        if ($object instanceof ledgerentry) {
            debugPrint("LEDGER ENTRY FILTER");
            $fieldFilter = ['entryDate' => ['operator' => 'BETWEEN', 'value' => ['2022-01-01', '2022-01-02']]];
        }
        $object->getList($fieldFilter);
        debugPrint("getNextId#{$object}#");
        assert($object->getNextId() >= 0);
    } catch (Exception $ex) {
        debugPrint($ex->getMessage());
    }
}

function runViews(ObjectViewer $viewer, DataObjectInterface $object)
{
    try {
        debugPrint("OBJECT: " . get_class($viewer));
        debugPrint($viewer->printObject());
        $fieldFilter = [];
        if ($object instanceof ledgerentry) {
            debugPrint("LEDGER ENTRY FILTER");
            $fieldFilter = ['entryDate' => ['operator' => 'BETWEEN', 'value' => ['2022-01-01', '2022-01-02']]];
        }
        debugPrint($viewer->printObjectList($object->getList($fieldFilter)));
    } catch (Exception $ex) {
        debugPrint($ex->getMessage());
    }
}
