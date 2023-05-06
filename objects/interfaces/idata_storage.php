<?php
interface idata_storage
{
    /**
     * Check if data storage needs to be updated
     * @return bool TRUE if data storage is up-to-date, FALSE if data storage needs updating
     */
    function check(): bool;
    /**
     * Updates data storage according to current schema
     * @return bool TRUE if update was successfull, FALSE otherwise
     */
    function update(): bool;
    /**
     * Populates data storage with random data
     */
    function populateRandomData(): void;
    /**
     * Returns message stored in object (errors, warnings, information etc)
     * @return string the messages stored in the object
     */
    function message(): string;
    /**
     * Adds a new message to the object
     * @param string $message a message to store on the object
     * @return string the value of the current message stored at the object, after adding the supplied message
     */
    function addMessage(string $message): string;
}
