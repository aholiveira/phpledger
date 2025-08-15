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