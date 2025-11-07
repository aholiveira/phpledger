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
use PHPLedger\Contracts\ReportInterface;
class report implements ReportInterface
{
    protected array $queryData;
    protected mysqli $db;
    public array $reportData = [];
    public array $columnHeaders;
    public array $dateFilters;
    public array $savings;
    public function __construct(\mysqli $dblink)
    {
        $this->db = $dblink;
    }
    protected function getData(string $query, &...$vars): array
    {
        $this->queryData = [];
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new \mysqli_sql_exception();
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

    public function getReport(array $params = []): self
    {
        global $objectFactory;
        $category = $objectFactory->entryCategory();
        $category_list = $category->getList(['parent_id' => ['operator' => '=', 'value' => '0']]);
        foreach ($category_list as $category) {
            $this->processCategory($category);
        }
        return $this;
    }
    protected function processCategory($category): void
    {
        if (!empty($this->queryData[$category->id])) {
            $this->reportData[$category->description] = [
                'id' => $category->id,
                'values' => $this->queryData[$category->id]
            ];
        }

        foreach ($category->children as $child) {
            if (!empty($this->queryData[$child->id])) {
                $this->reportData[$category->description]['children'][$child->description] = [
                    'id' => $child->id,
                    'values' => $this->queryData[$child->id]
                ];
                $this->updateSubtotal($category, $this->queryData[$child->id]);
            }
        }
    }
    protected function updateSubtotal($category, array $childValues): void
    {
        foreach ($childValues as $col_header => $value) {
            $this->reportData[$category->description]['id'] ??= $category->id;
            $this->reportData[$category->description]['values'] ??= [];
            $this->reportData[$category->description]['subtotal'][$col_header] ??= 0;
            $this->reportData[$category->description]['subtotal'][$col_header] += $value;
        }
    }
}
