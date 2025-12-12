<?php

namespace PHPLedger\Contracts;

interface UiBuilderInterface
{
    public function menu(array $text, array $menuLinks): void;
    public function footer(array $text, array $footer): void;
}
