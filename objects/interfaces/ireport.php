<?php

/**
 * Interface for the year_report object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
interface ireport
{
    /**
     * getReport
     * Calculates the report and returns the populated object
     * @param int $year Year to calculate the report for
     * @return ireport returns the populated object
     */
    public function getReport(array $params = []): ireport;
}
