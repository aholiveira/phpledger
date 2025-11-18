<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Util;
use \PHPLedger\Util\L10n;
use \PHPLedger\Util\Config;
class Html
{
    /**
     * Option list for year selection
     * If no end value is provided current year is used
     * If no selected value is provided, current year is selected
     */
    public static function yearOptions(?int $selected = null, int $start = 1990, ?int $end = null): string
    {
        return self::buildOptions($start, null === $end ? date("Y") : $end, null === $selected ? date("Y") : $selected);
    }
    /**
     * Option list for month selection
     * If no end value is provided current month is used
     * If no selected value is provided, current month is selected
     */
    public static function monthOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 12, null === $selected ? date("n") : $selected);
    }
    /**
     * Option list for day selection
     * If no end value is provided current day is used
     * If no selected value is provided, current day is selected
     */
    public static function dayOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 31, null === $selected ? date("d") : $selected);
    }
    public static function hourOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 23, null === $selected ? date("G") : $selected);
    }
    public static function minuteOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 59, null === $selected ? date("i") : $selected);
    }
    public static function buildOptions(int $start, int $end, ?string $selected = null): string
    {
        $retval = "";
        $length = strlen($end);
        for ($i = $start; $i <= $end; $i++) {
            $retval .= sprintf("<option value=\"%d\" %s>%0{$length}d</option>\n", $i, ($i === (int) $selected ? "selected" : ""), $i);
        }
        return $retval;
    }
    public static function errortext(string $message): never
    {
        ?>
        <p><?= $message ?></p>
        </div>
        </body>

        </html>
        <?php
        die();
    }
    public static function myalert(string $message): void
    {
        ?>
        <script type="text/javascript" defer>
            alert(<?= json_encode($message) ?>);
        </script>
        <?php
    }
    public static function header($pagetitle = ""): void
    {
        ?>
        <title>
            <?= htmlspecialchars((!empty($pagetitle) ? "$pagetitle - " : "") . config::get("title")) ?>
        </title>
        <script>
            document.cookie = "timezone=" + Intl.DateTimeFormat().resolvedOptions().timeZone + "; path=/";
        </script>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css">
        <?php
    }
    public static function footer(): void
    {
        ?>
        <footer>
            <div class='footer'>
                <span class='RCS'><a
                        href="https://github.com/aholiveira/phpledger"><?= l10n::l("version", VERSION) ?></a></span>
                <span class='RCS'
                    style="display: flex; align-items: center"><?= l10n::l("session_expires", date("Y-m-d H:i:s", $_SESSION['expires'])) ?>
                    <span style="margin-left: auto; display: flex;">
                        <?php if (l10n::$lang === 'pt-pt'): ?>
                            <a href="?lang=en-us">EN</a> | <span>PT</span>
                        <?php else: ?>
                            <span>EN</span> | <a href="?lang=pt-pt">PT</a>
                        <?php endif; ?>
                    </span>
                </span>
            </div>
        </footer>
        <?php
    }
    public static function menu(): void
    {
        ?>
        <aside class="menu">
            <nav>
                <ul>
                    <li><a id="ledger_entries" href="ledger_entries.php?lang=<?= l10n::$lang ?>"
                            aria-label="<?= l10n::l("ledger_entries") ?>"><?= l10n::l("ledger_entries") ?></a>
                    </li>
                    <li><a id="balance" href="balances.php?lang=<?= l10n::$lang ?>"
                            aria-label="<?= l10n::l("balances") ?>"><?= l10n::l("balances") ?></a>
                    </li>
                    <li><a id="accounts" href="accounts.php?lang=<?= l10n::$lang ?>"
                            aria-label="<?= l10n::l("accounts") ?>"><?= l10n::l("accounts") ?></a>
                    </li>
                    <li><a id="account_type" href="account_types_list.php?lang=<?= l10n::$lang ?>"
                            aria-label="<?= l10n::l("account_types") ?>"><?= l10n::l("account_types") ?></a>
                    </li>
                    <li><a id="entry_type" href="entry_types_list.php?lang=<?= l10n::$lang ?>"
                            aria-label="<?= l10n::l("entry_types") ?>"><?= l10n::l("entry_types") ?></a>
                    </li>
                    <li><a id="report_month" href="report_month.php?lang=<?= l10n::$lang ?>&year=<?= date("Y") ?>"
                            aria-label="<?= l10n::l("report_month") ?>"><?= l10n::l("report_month") ?></a>
                    </li>
                    <li><a id="report_year" href="report_year.php?lang=<?= l10n::$lang ?>&year=<?= date("Y") - 1 ?>"
                            aria-label="<?= l10n::l("report_year") ?>"><?= l10n::l("report_year") ?></a>
                    </li>
                    <li><a id="logout" href="index.php?lang=<?= l10n::$lang ?>&do_logout=1"
                            aria-label="<?= l10n::l("logout") ?>"><?= l10n::l("logout") ?></a>
                </ul>
            </nav>
        </aside>
        <?php
    }
    public static function languageSelector(bool $div = true): void
    {
        if ($div) {
            ?>
            <div>
                <?php
        }
        if (l10n::$lang === 'pt-pt'): ?>
                <a href="?lang=en-us">EN</a> | <span>PT</span>
            <?php else: ?>
                <span>EN</span> | <a href="?lang=pt-pt">PT</a>
            <?php endif;
        if ($div) {
            ?>
            </div>
            <?php
        }
    }
}
