<?php

namespace PHPLedger\Contracts;

interface DataStorageInterface
{
    /**
     * Returns message stored in object (errors, warnings, information etc)
     * @return string the messages stored in the object
     */
    public function message(): string;
    /**
     * Adds a new message to the object
     * @param string $message a message to store on the object
     * @return string the value of the current message stored at the object, after adding the supplied message
     */
    public function addMessage(string $message): string;
}
