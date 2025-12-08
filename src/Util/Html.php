<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Util;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Routing\Router;
use PHPLedger\Util\Config;
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
    public static function footer(ApplicationObjectInterface $app, string $currentAction): void
    {
        $expires = date("Y-m-d H:i:s", $app->session()->get('expires', time()));
        $lang = $app->l10n()->lang();

        $enLink = self::buildSafeLink($app->l10n(), $currentAction, ['lang' => 'en-us']);
        $ptLink = self::buildSafeLink($app->l10n(), $currentAction, ['lang' => 'pt-pt']);
    ?>
        <footer>
            <div class='footer'>
                <span class='RCS'>
                    <a href="https://github.com/aholiveira/phpledger"
                        aria-label="<?= $app->l10n()->l("version", Version::string()) ?>">
                        <?= $app->l10n()->l("version", Version::string()) ?>
                    </a>
                </span>
                <span class='RCS' style="display: flex; align-items: center">
                    <?= $app->l10n()->l("session_expires", $expires) ?>
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
    public static function menu(L10nServiceInterface $l10n, bool $isAdmin = false): void
    {
        $lang = $l10n->lang();
        $links = [
            'ledger_entries' => $l10n->l("ledger_entries"),
            'balances'       => $l10n->l("balances"),
            'accounts'       => $l10n->l("accounts"),
            'account_types'  => $l10n->l("account_types"),
            'entry_types'    => $l10n->l("entry_types"),
            'report_month'   => $l10n->l("report_month"),
            'report_year'    => $l10n->l("report_year")
        ];
        if ($isAdmin) {
            $links['config'] = $l10n->l("configuration");
        }
        $links['logout'] = $l10n->l("logout");
    ?>
        <aside class="menu">
            <nav>
                <ul>
                    <?php foreach ($links as $action => $label):
                        $link = self::buildSafeLink($l10n, $action, ['lang' => $lang]);
                    ?>
                        <li><a id="<?= $action ?>" href="<?= $link ?>" aria-label="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>
<?php
    }

    public static function languageSelector(L10nServiceInterface $l10n, bool $div = true): void
    {
        $lang = $l10n->lang();
        $currentAction = $_GET['action'] ?? 'login';
        $otherLang = $lang === 'pt-pt' ? 'en-us' : 'pt-pt';
        $link = self::buildSafeLink($l10n, $currentAction, ['lang' => $otherLang]);

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

    private static function buildSafeLink(L10nServiceInterface $l10n, string $action, array $extraParams = []): string
    {
        $allowed = Router::getAllowedActions();
        if (!in_array($action, $allowed, true)) {
            $action = 'login';
        }

        $params = array_merge(['action' => $action, 'lang' => $l10n->lang()], $extraParams);
        return 'index.php?' . http_build_query($params);
    }
}
