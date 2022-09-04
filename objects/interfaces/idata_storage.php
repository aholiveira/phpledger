<?php
interface idata_storage
{
    function check(): bool;
    function update(): bool;
    function populateRandomData(): void;
    function message(): string;
    function addMessage(string $message): string;
}
