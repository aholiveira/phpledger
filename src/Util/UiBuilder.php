<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Util;

use PHPLedger\Contracts\UiBuilderInterface;

final class UiBuilder implements UiBuilderInterface
{
    public function menu(array $text, array $menuLinks, ?string $greeting = null): void
    {
?>
        <aside class="menu">
            <?php if (($displayGreeting = $greeting ?? ($text['hello'] ?? '')) !== ''): ?>
                <div class="menu-greeting"><?= htmlspecialchars($displayGreeting) ?></div>
            <?php endif ?>
            <nav>
                <ul>
                    <?php foreach ($menuLinks as $action => $link): ?>
                        <li><a id="<?= $action ?>" aria-label="<?= htmlspecialchars($text[$action] ?? '') ?>" href="<?= $link ?>"><?= htmlspecialchars($text[$action] ?? '') ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>
    <?php
    }

    public function footer(array $text, array $footer): void
    {
    ?>
        <footer class="footer">
            <span class="RCS"><a href="<?= $footer['repo'] ?? '' ?>" aria-label="<?= htmlspecialchars($footer['versionText'] ?? '') ?>"><?= htmlspecialchars($footer['versionText'] ?? '') ?></a></span>
            <span class="RCS" style="display: flex; align-items: center">
                <?= htmlspecialchars($footer['sessionExpires'] ?? '') ?>
                <span style="margin-left: auto; display: flex;"><?= $footer['languageSelectorHtml'] ?? '' ?></span>
            </span>
        </footer>
        <?php
    }

    public function notification(string $notification, bool $success): void
    {
        if (!empty($notification)) {
        ?>
            <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
                <?= $notification ?>
            </div>
            <script>
                const el = document.getElementById('notification');
                setTimeout(() => {
                    el.classList.add('hide');
                    el.addEventListener('transitionend', () => el.remove(), {
                        once: true
                    });
                }, 2500);
            </script>
<?php }
    }
}
