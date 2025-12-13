<?php

namespace PHPLedger\Util;

use PHPLedger\Contracts\UiBuilderInterface;

final class UiBuilder implements UiBuilderInterface
{
    public function menu(array $text, array $menuLinks): void
    {
?>
        <aside class="menu">
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
        <footer>
            <div class="footer">
                <span class="RCS"><a href="<?= $footer['repo'] ?? '' ?>" aria-label="<?= htmlspecialchars($footer['versionText'] ?? '') ?>"><?= htmlspecialchars($footer['versionText'] ?? '') ?></a></span>
                <span class="RCS" style="display:flex;align-items:center">
                    <?= htmlspecialchars($footer['sessionExpires'] ?? '') ?>
                    <span style="margin-left:auto;display:flex;"><?= $footer['languageSelectorHtml'] ?? '' ?></span>
                </span>
            </div>
        </footer>
<?php
    }
}
