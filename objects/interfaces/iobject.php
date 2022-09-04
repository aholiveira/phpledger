<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
interface iobject
{
    function validate(): bool;
    function error_message(): string;
    function save(): bool;
    function getFreeId(): int;
    function getAll(array $field_filter = array()): array;
    function getById(int $id): iobject;
}
