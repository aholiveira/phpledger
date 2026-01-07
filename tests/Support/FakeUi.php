<?php
namespace PHPLedgerTests\Support;

final class FakeUI
{
    public function menu(array $label, array $menu): void { echo '<menu />'; }
    public function footer(array $label, array $footer): void { echo '<footer />'; }
    public function notification(string $notification, bool $success): void { return; }
}
