<?php
include_once "common.php";
use \PHPLedger\Contracts\DataObjectInterface;
use \PHPLedger\Storage\Abstract\AbstractDataObject;
use \PHPLedger\Storage\MySql\MySqlStorage;
use \PHPLedger\Storage\ObjectFactory;
use \PHPLedger\Util\Logger;
use \PHPLedger\Views\ViewFactory;
$retval = true;
$classnames = [
    "account" => "account_view",
    "accounttype" => "account_type_view",
    "currency" => "",
    "defaults" => "",
    "EntryCategory" => "entry_category_view",
    "Ledger" => "",
    "LedgerEntry" => "ledger_entry_view",
    "user" => ""
];
$class_id = ["currency" => 1];
$reports = ["ReportMonth" => "report_month_view", "ReportYear" => "report_year_view"];
const PADDING = 35;
const PASSED = "\033[32mPASSED\033[0m";
const FAILED = "\033[31mFAILED\033[0m";
$logger = new Logger("validate.log");
print "Running tests\r\n\r\n";
$data_storage = new MySqlStorage();
print str_pad("Testing data storage ", constant("PADDING"), ".") . " : ";
if (!$data_storage->check()) {
    print "\033[33mUPDATE\033[0m\r\n";
    print $data_storage->message();
    print str_pad("Testing storage update ", constant("PADDING"), ".") . " : ";
    if ($data_storage->update()) {
        print constant("PASSED") . "\r\n";
        print $data_storage->message();
    } else {
        print constant("FAILED") . "\r\n";
        exit(1);
    }
} else {
    print constant("PASSED") . "\r\n";
}
function prepare_accounttype(): bool
{
    $retval = true;
    $object = ObjectFactory::accounttype();
    for ($id = 1; $id <= 5; $id++) {
        $object = $object->getById($id);
        if (!isset($object->id) || $object->id === $id) {
            $object->id = $id;
            $object->description = "Account type {$id}";
            $object->savings = false;
            $retval = $object->update() && $retval;
        }
    }
    return $retval;
}
function prepare_account(): bool
{
    $retval = true;
    $object = ObjectFactory::account();
    for ($id = 1; $id <= 5; $id++) {
        $object = $object->getById($id);
        if (!isset($object->id) || $object->id === $id) {
            $object->id = $id;
            $object->number = "Account number {$id}";
            $object->name = "Account name {$id}";
            $object->type_id = $id;
            $object->iban = str_pad($id, 20, $id);
            $object->swift = "SWIFT" . str_pad($id, 2, $id);
            $object->open_date = date("Y-m-d", mktime($hour = 0, $minute = 0, $second = 0, $month = 1, $day = $id, $year = date("Y")));
            $object->close_date = date("Y-m-d", mktime($hour = 0, $minute = 0, $second = 0, $month = 1, $day = $id, $year = date("Y") + 1));
            $object->active = 1;
            $retval = $object->update() && $retval;
        }
    }
    return $retval;
}
function prepare_entry_category(): bool
{
    $retval = true;
    $object = ObjectFactory::entryCategory();
    for ($id = 1; $id < 60; $id++) {
        $object->id = $id;
        $object->parent_id = $id < 10 ? 0 : (int) ($id / 10);
        $object->description = "entry category $id";
        $object->active = 1;
        $retval = $object->update() && $retval;
    }
    return $retval;
}
function prepare_ledger(): bool
{
    $retval = true;
    $object = ObjectFactory::ledger();
    for ($id = 1; $id <= 5; $id++) {
        $object->id = $id;
        $object->name = "ledger $id";
        $retval = $object->update() && $retval;
    }
    return $retval;
}
function prepare_ledgerentry(): bool
{
    $retval = true;
    $object = ObjectFactory::ledgerentry();
    for ($id = 1; $id < 60; $id++) {
        $object->id = $id;
        $object->entry_date = date("Y-m-d", mktime($hour = 0, null, null, date("m"), $id < 10 ? 1 : (int) ($id / 10 + 1)));
        $object->category_id = $id;
        $object->account_id = $id < 10 ? 1 : (int) ($id / 10);
        $object->currency_id = 1;
        $object->direction = ($id % 2 == 0 ? 1 : -1);
        $object->currency_amount = $id;
        $object->euro_amount = $object->direction * $object->currency_amount;
        $object->remarks = "Entry $id";
        $object->username = "admin";
        $retval = $object->update() && $retval;
    }
    return $retval;
}

print str_pad("Preparing data ", constant("PADDING"), ".") . " : ";
$retval = prepare_entry_category() && $retval;
$retval = prepare_accounttype() && $retval;
$retval = prepare_account() && $retval;
$retval = prepare_ledger() && $retval;
$retval = prepare_ledgerentry() && $retval;
if ($retval) {
    print constant("PASSED") . "\r\n";
} else {
    print constant("FAILED") . "\r\n";
    exit(1);
}
foreach ($classnames as $class => $view) {
    $id = 1;
    unset($object);
    unset($viewer);
    $object = ObjectFactory::$class();
    if (array_key_exists($class, $class_id)) {
        $id = $class_id[$class];
    }
    $retval = test_object($object, $id) && $retval;
    if (strlen($view) > 0) {
        $object = $object->getById($id);
        $viewer = ViewFactory::instance()->$view($object);
        $retval = test_view($viewer, $object) && $retval;
    }
    $retval = run_additional($object, isset($viewer) ? $viewer : null) && $retval;
}
foreach ($reports as $report => $view) {
    $retval = test_report($report, $view) && $retval;
}
print "\r\n" . str_pad("Test results ", constant("PADDING"), ".") . " : " . ($retval ? constant("PASSED") : constant("FAILED")) . "\r\n";
exit($retval ? 0 : 1);

function run_additional($object, $viewer = null)
{
    $retval = true;
    switch (get_class($object)) {
        case 'account':
            $balance = $object->getBalanceOnDate(new DateTime());
            $retval = assert(is_float($balance['income'])) && $retval;
            $retval = assert(is_float($balance['expense'])) && $retval;
            $retval = assert(is_float($balance['balance'])) && $retval;
            $retval = assert(strlen($viewer->printObjectList($object->getList(['activa' => ['operator' => '=', 'value' => '1']]))) > 0) && $retval;
            break;
        default:
            $retval = true;
            break;
    }
    return $retval;
}
function test_report($report, $view)
{
    $retval = true;
    print str_pad("Testing {$report} ", constant("PADDING"), ".") . " : ";
    $object = ObjectFactory::$report();
    #assert(is_a($object->getReport(["year" => 2023]), $report));
    $viewer = ViewFactory::instance()->$view($object);
    #$retval = assert(!empty($viewer->printAsTable())) && $retval;
    print ($retval ? constant("PASSED") : constant("FAILED")) . "\r\n";
    return $retval;
}
function test_object(AbstractDataObject $object, $id = 1)
{
    $retval = true;
    global $logger;
    try {
        print str_pad("Testing {$object} ", constant("PADDING"), ".") . " : ";
        $object = $object->getById($id);
        if (isset($object->id)) {
            $retval = assert($object->id === $id, "getById") && $retval;
        }
        $retval = assert($object->update() === true, "save#{$object}#");
        $field_filter = [];
        if ($object instanceof ledgerentry) {
            $field_filter[] = ['entry_date' => ['operator' => 'BETWEEN', 'value' => date("Y-01-01 ") . "' AND '" . date("Y-12-31")]];
        }
        $retval = @assert(sizeof($object->getList($field_filter)) > 0, "getList#{$object}#") && $retval;
        $retval = @assert($object->getNextId() >= 0, "getNextId#{$object}#") && $retval;
        print ($retval ? constant("PASSED") : constant("FAILED")) . "\r\n";
    } catch (Exception $ex) {
        $logger->dump($ex->getMessage());
        $logger->dump($ex->getTraceAsString());
        $logger->dump($object, "OBJECT");
        $retval = false;
    }
    return $retval;
}
function test_view(ObjectViewer $viewer, DataObjectInterface $object)
{
    $retval = true;
    global $logger;
    try {
        print str_pad("Testing " . get_class($viewer) . " ", constant("PADDING"), ".") . " : ";
        $retval = assert(!empty($viewer->printObject())) && $retval;
        $field_filter = [];
        if ($object instanceof ledgerentry) {
            $field_filter[] = ['entry_date' => ['operator' => 'BETWEEN', 'value' => "2022-01-01' AND '2022-01-02"]];
        }
        $retval = @assert(!empty($viewer->printObjectList($object->getList($field_filter))), "#printObjectList#") && $retval;
        $method = "printForm";
        if (method_exists($viewer, $method)) {
            $retval = @assert(!empty(@$viewer->$method()), "#{$method}#") && $retval;
        }
        print ($retval ? constant("PASSED") : constant("FAILED")) . "\r\n";
    } catch (Exception $ex) {
        $logger->dump("EXCEPTION");
        $logger->dump($ex->getMessage());
        $logger->dump($object, "OBJECT");
        $retval = false;
    }
    return $retval;
}
