<?php

/**
 * Year Report
 * Class to generate a year report with a summary of income and expense per month
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class report implements ireport
{
    protected array $queryData;
    protected mysqli $db;
    public array $reportData = array();
    public array $columnHeaders;
    public array $dateFilters;
    public array $savings;
    public function __construct(\mysqli $dblink)
    {
        $this->db = $dblink;
    }
    protected function getData(string $query, &...$vars): array
    {
        $this->queryData = array();
        if (!($this->db->ping())) {
            return $this->queryData;
        }
        $stmt = $this->db->prepare($query);
        if ($stmt == false) {
            debug_print($this->db->error);
            throw new \mysqli_sql_exception($this->db->error);
        }
        $stmt->bind_param(str_repeat('s', sizeof($vars)), ...$vars);
        $stmt->execute();
        $stmt->bind_result($row_header, $col_header, $value);
        while ($stmt->fetch()) {
            $this->queryData[$row_header][$col_header] = $value;
        }
        $stmt->close();
        return $this->queryData;
    }
    public function getReport(array $params = array()): report
    {
        global $object_factory;
        $category = $object_factory->entry_category();
        $category_list = $category->getList(array('parent_id' => array('operator' => '=', 'value' => '0')));
        foreach ($category_list as $category) {
            if (array_key_exists($category->id, $this->queryData) && sizeof($this->queryData[$category->id]) > 0) {
                $this->reportData[$category->description]['id'] = $category->id;
                $this->reportData[$category->description]['values'] = $this->queryData[$category->id];
            }
            foreach ($category->children as $child) {
                if (array_key_exists($child->id, $this->queryData) && sizeof($this->queryData[$child->id]) > 0) {
                    $this->reportData[$category->description]['children'][$child->description]['id'] = $child->id;
                    $this->reportData[$category->description]['children'][$child->description]['values'] = $this->queryData[$child->id];
                    foreach ($this->queryData[$child->id] as $col_header => $value) {
                        if (!array_key_exists($category->description, $this->reportData)) {
                            $this->reportData[$category->description] = array();
                        }
                        if (!array_key_exists('id', $this->reportData[$category->description])) {
                            $this->reportData[$category->description]['id'] = $category->id;
                        }
                        if (!array_key_exists('values', $this->reportData[$category->description])) {
                            $this->reportData[$category->description]['values'] = array();
                        }
                        if (!array_key_exists('subtotal', $this->reportData[$category->description])) {
                            $this->reportData[$category->description]['subtotal'] = array();
                        }
                        if (!array_key_exists($col_header, $this->reportData[$category->description]['subtotal'])) {
                            $this->reportData[$category->description]['subtotal'][$col_header] = 0;
                        }
                        $this->reportData[$category->description]['subtotal'][$col_header] += $value;
                    }
                }
            }
        }
        return $this;
    }
}
