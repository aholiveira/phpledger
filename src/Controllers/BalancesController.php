<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\BalancesView;
use PHPLedger\Views\ViewFactory;

final class BalancesController extends AbstractViewController
{
    protected function handle(): void
    {
        $object = ObjectFactory::account();
        $viewer = ViewFactory::instance()->accountBalanceView($object);
        $reportData = $viewer->printObjectList($object->getList(['activa' => ['operator' => '=', 'value' => '1']]));
        $view = new BalancesView;
        $view->render($reportData);
    }
}
