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
<div class="menu">
    <nav>
        <ul>
            <li><a href="ledger_entries.php?from=menu#last">Movimentos</a></li>
            <li><a href="saldos.php">Saldos</a></li>
            <li><a href="contas.php">Contas</a></li>
            <li><a href="tipo_contas_lista.php">Tipo contas</a></li>
            <li><a href="tipo_mov_lista.php">Categorias movim.</a></li>
            <li><a href="report_month.php?year=<?php print date("Y"); ?>">Relat&oacute;rio mensal</a></li>
            <li><a href="report_year.php?year=<?php print date("Y") - 1; ?>">Relat&oacute;rio anual</a></li>
            <li><a href="index.php?do_logout=1">Sair</a>
        </ul>
    </nav>
</div>