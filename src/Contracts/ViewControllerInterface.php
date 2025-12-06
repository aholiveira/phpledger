<?php

namespace PHPLedger\Contracts;

/**
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 */
interface ViewControllerInterface
{
    public function handleRequest(ApplicationObjectInterface $app, RequestInterface $request): void;
}
