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
<html>

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
    </script>
</head>

<body>
    <div class="maingrid">
        <?php
        include constant("ROOT_DIR") . "/menu_div.php";
        ?>
        <div id="header" class="header">
            <form name="filtro" action="report_month.php" method="GET">
                <p>Ano <input type="text" name="year" maxlength="4" size="6" value="<?php print $year; ?>" /></p>
                <p><input type="submit" value="Obter"></p>
            </form>
        </div>
        <div class="main" id="main">
            <table class="lista report_month">
                <?php print $reportHtml->printAsTable(); ?>
            </table>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>