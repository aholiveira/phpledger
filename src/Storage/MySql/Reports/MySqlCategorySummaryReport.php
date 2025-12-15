<?php

namespace PHPLedger\Storage\MySql\Reports;

use DateTimeImmutable;
use mysqli_sql_exception;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Reports\CategorySummaryReport;
use PHPLedger\Storage\MySql\MySqlStorage;

final class MySqlCategorySummaryReport extends CategorySummaryReport
{
    public function fetch(DateTimeImmutable $from, DateTimeImmutable $to, string $period): array
    {
        $group = strtolower($period);
        if ($group !== 'month' && $group !== 'year') {
            throw new PHPLedgerException('Invalid period parameter');
        }

        $sqlCategory = "SELECT
                    movimentos.categoryId,
                    tipo_mov.parentId,
                    COALESCE(parents.`description`, '') AS parentDescription,
                    tipo_mov.`description` AS categoryDescription,
                    CASE WHEN ? = 'month' THEN MONTH(movimentos.entryDate) ELSE YEAR(movimentos.entryDate) END AS groupColumn,
                    ROUND(SUM(movimentos.euroAmount), 2) AS amountSum
                FROM movimentos
                LEFT JOIN tipo_mov ON tipo_mov.id = movimentos.categoryId
                LEFT JOIN tipo_mov AS parents ON tipo_mov.parentId = parents.id AND parents.parentId = 0
                WHERE movimentos.entryDate BETWEEN ? AND ?
                GROUP BY tipo_mov.parentId, parents.description, tipo_mov.description, movimentos.categoryId,
                         CASE WHEN ? = 'month' THEN MONTH(movimentos.entryDate) ELSE YEAR(movimentos.entryDate) END
                ";
        $sqlSavings = "SELECT
                    CASE WHEN ? = 'month'
                        THEN MONTH(movimentos.entryDate)
                        ELSE YEAR(movimentos.entryDate)
                        END AS `groupColumn`,
                    SUM(movimentos.euroAmount) AS amountSum
                    FROM
                        movimentos
                        LEFT JOIN contas ON contas.id = movimentos.accountId
                        LEFT JOIN tipo_contas ON tipo_contas.id = contas.typeId
                    WHERE
                        movimentos.entryDate BETWEEN ? AND ?
                        AND tipo_contas.savings = 1
                        GROUP BY CASE WHEN ? = 'month' THEN MONTH(movimentos.entryDate) ELSE YEAR(movimentos.entryDate) END
                        ";
        try {
            return [
                'category' => $this->getRows($sqlCategory, $from, $to, $group),
                'savings' => $this->getRows($sqlSavings, $from, $to, $group)
            ];
        } catch (\Exception $ex) {
            throw new PHPLedgerException($ex->getMessage());
        }
    }
    private function getRows(string $sql, DateTimeImmutable $from, DateTimeImmutable $to, string $group): array
    {
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if (!$stmt) {
                throw new mysqli_sql_exception("Failed to prepare statement");
            }
            $fromStr = $from->format('Y-m-d');
            $toStr = $to->format('Y-m-d');
            $stmt->bind_param('ssss', $group, $fromStr, $toStr, $group);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $ex) {
            throw new PHPLedgerException($ex->getMessage());
        }
    }
}
