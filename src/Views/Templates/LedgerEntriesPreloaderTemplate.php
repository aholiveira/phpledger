<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class LedgerEntriesPreloaderTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?> ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
            <script src="assets/ledger_entries.js"> </script>
        </head>

        <body>
            <?php if (!empty($label['notification'])): ?>
                <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
                    <?= $label['notification'] ?>
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
            <?php endif ?>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
        <?php
    }
}
