<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Util;
use PHPLedger\Util\Config;
use PHPLedger\Util\L10n;
use PHPLedger\Version;

final class Html
{
    public static function yearOptions(?int $selected = null, int $start = 1990, ?int $end = null): string
    {
        return self::buildOptions($start, $end ?? date("Y"), $selected ?? (int) date("Y"));
    }
    public static function monthOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 12, $selected ?? date("n"));
    }
    public static function dayOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 31, $selected ?? date("d"));
    }
    public static function hourOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 23, $selected ?? date("G"));
    }
    public static function minuteOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 59, $selected ?? date("i"));
    }
    public static function buildOptions(int $start, int $end, ?string $selected = null): string
    {
        $selectedValue = (int) $selected;
        $retval = "";
        $length = \strlen((string) $end);
        for ($i = $start; $i <= $end; $i++) {
            $s = $i === $selectedValue ? ' selected' : '';
            $retval .= \sprintf("<option value=\"%d\"%s>%0{$length}d</option>\n", $i, $s, $i);
        }
        return $retval;
    }
    public static function errortext(string $message, $exit = true): void
    {
        ?>
        <p><?= htmlspecialchars($message) ?></p>
        </div>
        </body>

        </html>
        <?php
        if ($exit) {
            exit;
        }
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
        $title = trim($pagetitle) !== '' ? "$pagetitle - " : '';
        $fullTitle = $title . Config::get("title");
        ?>
        <title><?= htmlspecialchars($fullTitle) ?></title>
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
        $expires = date("Y-m-d H:i:s", $_SESSION['expires'] ?? time());
        ?>
        <footer>
            <div class='footer'>
                <span class='RCS'><a href="https://github.com/aholiveira/phpledger"
                        aria-label="<?= L10n::l("version", Version::string()) ?>"><?= L10n::l("version", Version::string()) ?></a></span>
                <span class='RCS' style="display: flex; align-items: center"><?= L10n::l("session_expires", $expires) ?>
                    <span style="margin-left: auto; display: flex;">
                        <?php if (L10n::$lang === 'pt-pt'): ?>
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
        $lang = L10n::$lang;
        ?>
        <aside class="menu">
            <nav>
                <ul>
                    <li><a id="ledger_entries" href="ledger_entries.php?lang=<?= $lang ?>"
                            aria-label="<?= L10n::l("ledger_entries") ?>"><?= L10n::l("ledger_entries") ?></a>
                    </li>
                    <li><a id="balance" href="balances.php?lang=<?= $lang ?>"
                            aria-label="<?= L10n::l("balances") ?>"><?= L10n::l("balances") ?></a>
                    </li>
                    <li><a id="accounts" href="accounts.php?lang=<?= $lang ?>"
                            aria-label="<?= L10n::l("accounts") ?>"><?= L10n::l("accounts") ?></a>
                    </li>
                    <li><a id="account_type" href="account_types_list.php?lang=<?= $lang ?>"
                            aria-label="<?= L10n::l("account_types") ?>"><?= L10n::l("account_types") ?></a>
                    </li>
                    <li><a id="entry_type" href="entry_types_list.php?lang=<?= $lang ?>"
                            aria-label="<?= L10n::l("entry_types") ?>"><?= L10n::l("entry_types") ?></a>
                    </li>
                    <li><a id="report_month" href="report_month.php?lang=<?= $lang ?>&year=<?= date("Y") ?>"
                            aria-label="<?= L10n::l("report_month") ?>"><?= L10n::l("report_month") ?></a>
                    </li>
                    <li><a id="report_year" href="report_year.php?lang=<?= $lang ?>&year=<?= date("Y") - 1 ?>"
                            aria-label="<?= L10n::l("report_year") ?>"><?= L10n::l("report_year") ?></a>
                    </li>
                    <li><a id="logout" href="index.php?lang=<?= $lang ?>&do_logout=1"
                            aria-label="<?= L10n::l("logout") ?>"><?= L10n::l("logout") ?></a>
                </ul>
            </nav>
        </aside>
        <?php
    }
    public static function languageSelector(bool $div = true): void
    {
        $lang = L10n::$lang;
        if ($div) {
            ?>
            <div>
                <?php
        }
        if ($lang === 'pt-pt'): ?>
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
