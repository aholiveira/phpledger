<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/contas_config.php";
$pagetitle = "Relat&oacute;rio mensal";
$year = date("Y");
if (array_key_exists("year", $_GET)) {
    $year = filter_input(INPUT_GET, "year", FILTER_VALIDATE_INT);
    if (!is_numeric($year) || ($year <= 1990 && $year >= 2100)) {
        $year = date("Y");
    }
}
$report = $object_factory->report_month();
$reportHtml = $view_factory->report_month_view($report);
$report->year = $year;
$report->getReport(array("year" => $year));
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
    <script>
        function toogleGroup(groupName) {
            var i, j, row, multiplier;
            row = document.getElementsByClassName(groupName);
            for (i = 0; i < row.length; i++) {
                if (row[i].style.display == "none") {
                    row[i].style.removeProperty('display');
                } else {
                    row[i].style.display = "none";
                }
            }
        }

        function toggle(visible) {
            element = document.getElementById(visible);
            if (element.style.display == "none") {
                element.style.removeProperty("display");
            } else {
                element.style.display = "none";
            }
            if (visible == "graph" && element.style.display != "none") {
                drawChart();
            }
        }
    </script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
    <div class="maingrid">
        <?php
        include constant("ROOT_DIR") . "/menu_div.php";
        ?>
        <div id="header" class="header">
            <form name="filtro" action="report_month.php" method="GET">
                <p>Ano <input type="text" name="year" maxlength="4" size="6" value="<?php print $year; ?>"></p>
                <p><input type="submit" value="Obter"></p>
            </form>
        </div>
        <div class="main" id="main">
            <div class="viewSelector" id="viewSelector">
                <button type="button" onclick="toggle('graph');" alt="toggle graph">Show graph</button>
                <button type="button" onclick="toggle('table');" alt="toggle table">Show table</button>
            </div>
            <div class="graph" id="graph" style="display: none; width: 99%"></div>
            <div class="table" id="table" style="display: initial; width: 99%">
                <table class="lista report_month">
                    <?php print $reportHtml->printAsTable();
                    ?>
                </table>
            </div>
            <?php
            $income_array[] = [];
            $expense_array[] = [];
            for ($month = 1; $month <= 12; $month++) {
                $income_array[$month] = 0;
                $expense_array[$month] = 0;
                foreach (array_keys($report->reportData) as $key) {
                    if (array_key_exists($month, $report->reportData[$key]["values"])) {
                        if ($report->reportData[$key]["values"][$month] > 0) {
                            $income_array[$month] += $report->reportData[$key]["values"][$month];
                        } else {
                            $expense_array[$month] += $report->reportData[$key]["values"][$month];
                        }
                    }
                    if (array_key_exists("children", $report->reportData[$key])) {
                        foreach ($report->reportData[$key]["children"] as $child)
                            if (array_key_exists($month, $child["values"])) {
                                if ($child["values"][$month] > 0) {
                                    $income_array[$month] += $child["values"][$month];
                                } else {
                                    $expense_array[$month] += $child["values"][$month];
                                }
                            }
                    }
                }
            }
            $table = [];
            $table['cols'] = [
                ["id" => "", 'label' => 'Month', 'type' => 'string'],
                ["id" => "", 'label' => 'Income', 'type' => 'number'],
                ["id" => "", 'label' => 'Expense', 'type' => 'number'],
                ["id" => "", 'label' => 'Savings', 'type' => 'number']
            ];
            $rows = [];
            for ($month = 1; $month <= 12; $month++) {
                $temp = [];
                $temp[] = ['v' => date("M", mktime(0, 0, 0, $month, 1))];
                $temp[] = ['v' => $income_array[$month]];
                $temp[] = ['v' => abs($expense_array[$month])];
                $temp[] = ['v' => array_key_exists($month, $report->savings) ? $report->savings[$month] : 0];
                $rows[] = ['c' => $temp];
            }
            $table['rows'] = $rows;
            foreach (array_keys($report->reportData) as $entry) {
                $sum[$entry] = array_sum($report->reportData[$entry]["values"]);
                if (array_key_exists("subtotal", $report->reportData[$entry])) {
                    $sum[$entry] += array_sum($report->reportData[$entry]['subtotal']);
                }
                if (array_key_exists("children", $report->reportData[$entry])) {
                    $sum_subentries[$entry] = [];
                    foreach (array_keys($report->reportData[$entry]["children"]) as $subentry) {
                        $sum_subentries[$entry][$subentry] = array_sum($report->reportData[$entry]["children"][$subentry]["values"]);
                    }
                }
            }
            $jsonTable = json_encode($table);
            ?>
            <script type="text/javascript">
                google.charts.load('current', {
                    'packages': ['corechart']
                });

                // Set a callback to run when the Google Visualization API is loaded.
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                    var data = new google.visualization.DataTable(<?php print $jsonTable; ?>);
                    var graph = new google.visualization.ColumnChart(document.getElementById('graph'));
                    var options = {
                        height: 500,
                        animation: {
                            startup: true,
                            duration: 1000,
                            easing: 'out',
                        },
                        interactivity: true,
                        selectionMode: 'multiple',
                        legend: {
                            position: 'bottom'
                        },
                        title: 'Monthly summary',
                        vAxis: {
                            title: 'Euros'
                        },
                        hAxis: {
                            title: 'Month'
                        },
                    }
                    graph.draw(data, options);
                };
            </script>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>