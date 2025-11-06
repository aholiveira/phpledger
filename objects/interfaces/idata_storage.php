<?php
interface iDataStorage
{
    /**
     * Check if data storage needs to be updated
     * @return bool true if data storage is up-to-date, FALSE if data storage needs updating
     */
    public function check(): bool;
    /**
     * Updates data storage according to current schema
     * @return bool true if update was successfull, FALSE otherwise
     */
    public function update(): bool;
    /**
     * Populates data storage with random data
     */
    public function populateRandomData(): void;
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
