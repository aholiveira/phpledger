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
print("Running tests...\r\n");
$data_storage = new mysql_storage($config);
if (!$data_storage->check()) {
    print "DB NEEDS UPDATE";
    print $data_storage->message();
    if ($data_storage->update()) {
        print "UPDATE SUCCESSFULL";
        print $data_storage->message();
    } else {
        print "UPDATE FAILED";
        exit(0);
    }
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
print "RESULT: " . ($retval ? "\033[32mPASSED\033[0m" : "\033[31mFAILED\033[0m") . "\r\n";
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
    print(str_pad("Testing {$report} ...", 50));
    $object = $object_factory->$report();
    assert(is_a($object->getReport(array("year" => 2023)), $report));
    $viewer = $view_factory->$view($object);
    $retval = assert(strlen($viewer->printAsTable()) > 0) && $retval;
    print ($retval ?  "\033[32mPASSED\033[0m" : "\033[31mFAILED\033[0m") . "\r\n";
    return $retval;
}
function test_object(mysql_object $object, $id = 1)
{
    $retval = true;
    try {
        print(str_pad("Testing {$object} ...", 50));
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
        print ($retval ?  "\033[32mPASSED\033[0m" : "\033[31mFAILED\033[0m") . "\r\n";
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
        print(str_pad("Testing " . get_class($viewer) . " ...", 50));
        $retval = (assert(strlen($viewer->printObject()) > 0) && $retval);
        $field_filter = array();
        if ($object instanceof ledgerentry) {
            $field_filter = array('data_mov' => array('operator' => 'BETWEEN', 'value' => "'2022-01-01' AND '2022-01-02'"));
        }
        $retval = (@assert(strlen($viewer->printObjectList($object->getList($field_filter))) > 0, "#printObjectList#") && $retval);
        $method = "printForm";
        $assert = true;
        if (method_exists($viewer, $method)) {
            $retval = (@assert(strlen(@$viewer->$method()) > 0, "#{$method}#") && $retval);
        }
        print ($retval ?  "\033[32mPASSED\033[0m" : "\033[31mFAILED\033[0m") . "\r\n";
    } catch (Exception $ex) {
        debug_print("EXCEPTION");
        debug_print($ex->getMessage());
        print_var($object, "OBJECT", false);
        $retval = false;
    }
    return $retval;
}
