<?php

/**
 * Interface for UI building services.
 *
 * Provides methods to render menus and footers with given content and links.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface UiBuilderInterface
{
    /**
     * Render a menu with the specified text and links.
     *
     * @param array $text      Text elements for the menu
     * @param array $menuLinks Menu links
     */
    public function menu(array $text, array $menuLinks): void;

    /**
     * Render a footer with the specified text and links.
     *
     * @param array $text   Text elements for the footer
     * @param array $footer Footer links
     */
    public function footer(array $text, array $footer): void;
}
