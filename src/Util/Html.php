<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Util;

use PHPLedger\Domain\User;
use PHPLedger\Routing\Router;
use PHPLedger\Storage\ObjectFactory;
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
    public static function errortext(string $message, bool $exit = true): void
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
    public static function header(): void
    {
    ?>
        <script>
            document.cookie = "timezone=" + Intl.DateTimeFormat().resolvedOptions().timeZone + "; path=/";
        </script>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/styles.css">
    <?php
    }
    public static function title(string $pagetitle = "")
    {
        $title = trim($pagetitle) !== '' ? "$pagetitle - " : '';
        $fullTitle = $title . Config::get("title");
        return  htmlspecialchars($fullTitle);
    }
    public static function footer(): void
    {
        $expires = date("Y-m-d H:i:s", $_SESSION['expires'] ?? time());
        $lang = L10n::$lang;
        $currentAction = $_GET['action'] ?? 'login';

        $enLink = self::buildSafeLink($currentAction, ['lang' => 'en-us']);
        $ptLink = self::buildSafeLink($currentAction, ['lang' => 'pt-pt']);
    ?>
        <footer>
            <div class='footer'>
                <span class='RCS'>
                    <a href="https://github.com/aholiveira/phpledger"
                        aria-label="<?= L10n::l("version", Version::string()) ?>">
                        <?= L10n::l("version", Version::string()) ?>
                    </a>
                </span>
                <span class='RCS' style="display: flex; align-items: center">
                    <?= L10n::l("session_expires", $expires) ?>
                    <span style="margin-left: auto; display: flex;">
                        <?php if ($lang === 'pt-pt'): ?>
                            <a href="<?= $enLink ?>">EN</a> | <span>PT</span>
                        <?php else: ?>
                            <span>EN</span> | <a href="<?= $ptLink ?>">PT</a>
                        <?php endif; ?>
                    </span>
                </span>
            </div>
        </footer>
    <?php
    }

    /**
     * Renders the main menu
     * @return void
     */
    public static function menu(): void
    {
        $lang = L10n::$lang;
        $userName = $_SESSION['app']['user'] ?? null;
        $user = $userName ? ObjectFactory::user()::getByUsername($userName) : null;
        $links = [
            'ledger_entries' => L10n::l("ledger_entries"),
            'balances'       => L10n::l("balances"),
            'accounts'       => L10n::l("accounts"),
            'account_types'  => L10n::l("account_types"),
            'entry_types'    => L10n::l("entry_types"),
            'report_month'   => L10n::l("report_month"),
            'report_year'    => L10n::l("report_year")
        ];
        if ($user && $user->hasRole(User::USER_ROLE_ADM)) {
            $links['config'] = L10n::l("configuration");
        }
        $links['logout'] = L10n::l("logout");
    ?>
        <aside class="menu">
            <nav>
                <ul>
                    <?php foreach ($links as $action => $label):
                        $link = self::buildSafeLink($action, ['lang' => $lang]);
                    ?>
                        <li><a id="<?= $action ?>" href="<?= $link ?>" aria-label="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>
<?php
    }

    public static function languageSelector(bool $div = true): void
    {
        $lang = L10n::$lang;
        $currentAction = $_GET['action'] ?? 'login';
        $otherLang = $lang === 'pt-pt' ? 'en-us' : 'pt-pt';
        $link = self::buildSafeLink($currentAction, ['lang' => $otherLang]);

        if ($div) {
            echo '<div>';
        }

        if ($lang === 'pt-pt') {
            echo "<a href=\"$link\">EN</a> | <span>PT</span>";
        } else {
            echo "<span>EN</span> | <a href=\"$link\">PT</a>";
        }

        if ($div) {
            echo '</div>';
        }
    }

    private static function buildSafeLink(string $action, array $extraParams = []): string
    {
        $allowed = Router::getAllowedActions();
        if (!in_array($action, $allowed, true)) {
            $action = 'login';
        }

        $params = array_merge(['action' => $action, 'lang' => L10n::$lang], $extraParams);
        return 'index.php?' . http_build_query($params);
    }
}
