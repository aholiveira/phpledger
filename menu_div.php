<!--
/**
 * Navigation menu
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
-->
<aside class="menu">
    <nav>
        <ul>
            <li><a id="ledger_entries" href="ledger_entries.php#last">Movimentos</a></li>
            <li><a id="balance" href="balances.php">Saldos</a></li>
            <li><a id="accounts" href="accounts.php">Contas</a></li>
            <li><a id="account_type" href="account_types_list.php">Tipo contas</a></li>
            <li><a id="entry_type" href="entry_types_list.php">Categorias movim.</a></li>
            <li><a id="report_month" href="report_month.php?year=<?php print date("Y"); ?>">Relat&oacute;rio mensal</a></li>
            <li><a id="report_year" href="report_year.php?year=<?php print date("Y") - 1; ?>">Relat&oacute;rio anual</a></li>
            <li><a id="logout" href="index.php?do_logout=1">Sair</a>
        </ul>
    </nav>
</aside>