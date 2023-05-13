<?php
include "common.php";
$retval = true;
$classnames = array(
    "account" => "account_view",
    "accounttype" => "account_type_view",
    "currency" => "",
    "defaults" => "",
    "entry_category" => "entry_category_view",
    "ledger" => "",
    "ledgerentry" => "ledger_entry_view",
    "user" => ""
);
$class_id = array(
    "currency" => "EUR"
);
$reports = array(
    "report_month" => "report_month_view",
    "report_year" => "report_year_view"
);
define("PADDING", 35);
define("PASSED", "\033[32mPASSED\033[0m");
define("FAILED", "\033[31mFAILED\033[0m");
print("Running tests\r\n\r\n");
$data_storage = new mysql_storage($config);
print(str_pad("Testing data storage ", constant("PADDING"), ".") . " : ");
if (!$data_storage->check()) {
    print "\033[33mUPDATE\033[0m\r\n";
    print $data_storage->message();
    print(str_pad("Testing storage update ", constant("PADDING"), ".") . " : ");
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
    global $object_factory;
    $retval = true;
    $object = $object_factory->accounttype();
    for ($id = 1; $id <= 5; $id++) {
        $object->getById($id);
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
    global $object_factory;
    $retval = true;
    $object = $object_factory->account();
    for ($id = 1; $id <= 5; $id++) {
        $object->getById($id);
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
    global $object_factory;
    $retval = true;
    $object = $object_factory->entry_category();
    for ($id = 1; $id < 60; $id++) {
        $object->id = $id;
        $object->parent_id = $id < 10 ? 0 : ($id / 10);
        $object->description = "entry category $id";
        $object->active = 1;
        $retval = $object->update() && $retval;
    }
    return $retval;
}
function prepare_ledger(): bool
{
    global $object_factory;
    $retval = true;
    $object = $object_factory->ledger();
    for ($id = 1; $id <= 5; $id++) {
        $object->id = $id;
        $object->name = "ledger $id";
        $retval = $object->update() && $retval;
    }
    return $retval;
}
function prepare_ledgerentry(): bool
{
    global $object_factory;
    $retval = true;
    $object = $object_factory->ledgerentry();
    for ($id = 1; $id < 60; $id++) {
        $object->id = $id;
        $object->entry_date = date("Y-m-d", mktime($hour = 0, null, null, date("m"), $id < 10 ? 1 : ($id / 10 + 1)));
        $object->category_id = $id;
        $object->account_id = $id < 10 ? 1 : $id / 10;
        $object->currency_id = "EUR";
        $object->direction = ($id % 2 == 0 ? 1 : -1);
        $object->currency_amount = $id;
        $object->euro_amount = $object->direction * $object->currency_amount;
        $object->remarks = "Entry $id";
        $object->username = "admin";
        $retval = $object->update() && $retval;
    }
    return $retval;
}

print(str_pad("Preparing data ", constant("PADDING"), ".") . " : ");
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
    $object = $object_factory->$class();
    if (array_key_exists($class, $class_id)) $id = $class_id[$class];
    $retval = (test_object($object, $id) && $retval);
    if (strlen($view) > 0) {
        $viewer = $view_factory->$view($object);
        $retval = test_view($viewer, $object) && $retval;
    }
    $retval = (run_additional($object, isset($viewer) ? $viewer : null) && $retval);
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
            $retval = assert(strlen($viewer->printObjectList($object->getList(array('activa' => array('operator' => '=', 'value' => '1'))))) > 0) && $retval;
            break;
    }
    return $retval;
}
function test_report($report, $view)
{
    global $object_factory;
    global $view_factory;
    $retval = true;
    print(str_pad("Testing {$report} ", constant("PADDING"), ".") . " : ");
    $object = $object_factory->$report();
    assert(is_a($object->getReport(array("year" => 2023)), $report));
    $viewer = $view_factory->$view($object);
    $retval = assert(!empty($viewer->printAsTable())) && $retval;
    print ($retval ? constant("PASSED") : constant("FAILED")) . "\r\n";
    return $retval;
}
function test_object(mysql_object $object, $id = 1)
{
    $retval = true;
    try {
        print(str_pad("Testing {$object} ", constant("PADDING"), ".") . " : ");
        $object->getById($id);
        if (isset($object->id)) {
            $retval = (assert($object->id === $id, "getById") && $retval);
        }
        $retval = (assert($object->update() === true, "save#{$object}#"));
        $field_filter = array();
        if ($object instanceof ledgerentry) {
            $field_filter = array('data_mov' => array('operator' => 'BETWEEN', 'value' => "'2023-01-01' AND '2023-12-31'"));
        }
        $retval = (@assert(sizeof($object->getList($field_filter)) > 0, "getList#{$object}#") && $retval);
        $retval = (@assert($object->getNextId() >= 0, "getNextId#{$object}#") && $retval);
        print ($retval ? constant("PASSED") : constant("FAILED")) . "\r\n";
    } catch (Exception $ex) {
        debug_print($ex->getMessage());
        debug_print($ex->getTraceAsString());
        print "EXCEPTION";
        $retval = false;
    }
    return $retval;
}
function test_view(object_viewer $viewer, iobject $object)
{
    $retval = true;
    try {
        print(str_pad("Testing " . get_class($viewer) . " ", constant("PADDING"), ".") . " : ");
        $retval = (assert(!empty($viewer->printObject())) && $retval);
        $field_filter = array();
        if ($object instanceof ledgerentry) {
            $field_filter = array('data_mov' => array('operator' => 'BETWEEN', 'value' => "'2022-01-01' AND '2022-01-02'"));
        }
        $retval = (@assert(!empty($viewer->printObjectList($object->getList($field_filter))), "#printObjectList#") && $retval);
        $method = "printForm";
        $assert = true;
        if (method_exists($viewer, $method)) {
            $retval = (@assert(!empty(@$viewer->$method()), "#{$method}#") && $retval);
        }
        print ($retval ? constant("PASSED") : constant("FAILED")) . "\r\n";
    } catch (Exception $ex) {
        debug_print("EXCEPTION");
        debug_print($ex->getMessage());
        print_var($object, "OBJECT", false);
        $retval = false;
    }
    return $retval;
}
