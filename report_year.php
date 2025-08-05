<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/contas_config.php";
$pagetitle = "Relat&oacute;rio anual";
$first_year = date("Y") - 1;
$last_year = date("Y");
if (array_key_exists("first_year", $_GET)) {
    $first_year = filter_input(INPUT_GET, "first_year", FILTER_VALIDATE_INT);
    if (!is_numeric($first_year) || ($first_year <= 1990 && $first_year >= 2100)) {
        $last_year = date("Y");
    }
}
if (array_key_exists("last_year", $_GET)) {
    $last_year = filter_input(INPUT_GET, "last_year", FILTER_VALIDATE_INT);
    if (!is_numeric($last_year) || ($last_year <= 1990 && $last_year >= 2100)) {
        $last_year = date("Y");
    }
}
$report = $object_factory->report_year();
$reportHtml = $view_factory->report_year_view($report);
$report->getReport(array("first_year" => $first_year, "last_year" => $last_year));
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
    </script>
</head>

<body>
    <div class="maingrid">
        <?php
        include ROOT_DIR . "/menu_div.php";
        ?>
        <div id="header" class="header">
            <form name="filtro" action="report_year.php" method="GET">
                <p>Ano inicial <input type="text" name="first_year" maxlength="4" size="6"
                        value="<?php print $first_year; ?>"></p>
                <p>Ano final <input type="text" name="last_year" maxlength="4" size="6"
                        value="<?php print $last_year; ?>"></p>
                <p><input type="submit" value="Obter"></p>
            </form>
        </div>
        <div class="main" id="main">
            <div class="report_year">
                <table class="lista report_year">
                    <?php print $reportHtml->printAsTable(); ?>
                </table>
            </div>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>