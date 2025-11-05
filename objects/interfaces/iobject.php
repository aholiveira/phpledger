<?php

/**
 * Generic data object interface - Common interface for all data objects
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
interface iObject
{
    /**
     * @property int id
     */
    /**
     * Validates if object contains valid data
     * @return bool true if object is valid, false otherwise
     */
    public function validate(): bool;
    public function errorMessage(): string;
    public function update(): bool;
    public function delete(): bool;
    public static function getNextId(): int;
    public static function getList(array $field_filter = []): array;
    public static function getById(int $id): ?iObject;
}
