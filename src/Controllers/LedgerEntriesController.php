<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Views\LedgerEntriesView;

final class LedgerEntriesController extends AbstractViewController
{
    protected function handle(): void
    {
        $view = new LedgerEntriesView;
        $view->render($this->app);
    }
}
