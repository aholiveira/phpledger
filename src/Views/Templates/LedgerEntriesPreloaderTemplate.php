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
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($pagetitle, $appTitle) ?></title>
            <?php Html::header(); ?>
            <script src="assets/js/ledger_entries.js"> </script>
        </head>

        <body>
            <?php $ui->notification($label['notification'], $success) ?>
            <div id="preloader">
                <div class="spinner"></div>
            </div>
            <div class="maingrid">
        <?php
    }
}
