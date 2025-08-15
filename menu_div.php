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
            <li><a id="ledger_entries"
                    href="ledger_entries.php?lang=<?= l10n::$lang ?>"><?= l10n::l("ledger_entries") ?></a></li>
            <li><a id="balance" href="balances.php?lang=<?= l10n::$lang ?>"><?= l10n::l("balances") ?></a></li>
            <li><a id="accounts" href="accounts.php?lang=<?= l10n::$lang ?>"><?= l10n::l("accounts") ?></a></li>
            <li><a id="account_type"
                    href="account_types_list.php?lang=<?= l10n::$lang ?>"><?= l10n::l("account_types") ?></a></li>
            <li><a id="entry_type" href="entry_types_list.php?lang=<?= l10n::$lang ?>"><?= l10n::l("entry_types") ?></a>
            </li>
            <li><a id="report_month"
                    href="report_month.php?lang=<?= l10n::$lang ?>&year=<?= date("Y") ?>"><?= l10n::l("report_month") ?></a>
            </li>
            <li><a id="report_year"
                    href="report_year.php?lang=<?= l10n::$lang ?>&year=<?= date("Y") - 1 ?>"><?= l10n::l("report_year") ?></a>
            </li>
            <li><a id="logout" href="index.php?lang=<?= l10n::$lang ?>&do_logout=1"><?= l10n::l("logout") ?></a>
        </ul>
    </nav>
</aside>