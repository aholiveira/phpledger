<?php

/**
 * Interface for view templates.
 *
 * Provides a method to render templates with supplied data.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface ViewTemplateInterface
{
    /**
     * Render the template with the provided data.
     *
     * @param array $data Data to be used in the template
     */
    public function render(array $data): void;
}
