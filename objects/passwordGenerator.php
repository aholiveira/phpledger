<?php
// Adapted from https://alexwebdevelop.com/php-generate-random-secure-password/
class passwordGenerator
{
    // Alphabetic letters, lowercase
    private const LETTERS = 'abcdefghijklmnopqrstuvwxyz';

    // Digits
    private const DIGITS = '0123456789';

    // Special characters
    private const SPECIAL_CHARS = '!@#$%^&*()_+-={}[]|:;"<>,.?/';

    // The maximum similarity percentage
    private const MAX_SIMILARITY_PERC = 20;

    public static function generate(int $minLength = 4, int $maxLength = 32, array $diffStrings = [], int $maxSimilarityPerc = self::MAX_SIMILARITY_PERC): string
    {
        // List of usable characters
        $chars = self::LETTERS . mb_strtoupper(self::LETTERS) . self::DIGITS . self::SPECIAL_CHARS;

        // Set to true when a valid password is generated
        $passwordReady = false;

        while (!$passwordReady) {
            // The password
            $password = '';

            // Password requirements
            $hasLowercase = false;
            $hasUppercase = false;
            $hasDigit = false;
            $hasSpecialChar = false;

            // A random password length
            $length = random_int($minLength, $maxLength);

            while ($length > 0) {
                $length--;

                // Choose a random character and add it to the password
                $index = random_int(0, mb_strlen($chars) - 1);
                $char = $chars[$index];
                $password .= $char;

                // Verify the requirements
                $hasLowercase = $hasLowercase || (mb_strpos(self::LETTERS, $char) !== false);
                $hasUppercase = $hasUppercase || (mb_strpos(mb_strtoupper(self::LETTERS), $char) !== false);
                $hasDigit = $hasDigit || (mb_strpos(self::DIGITS, $char) !== false);
                $hasSpecialChar = $hasSpecialChar || (mb_strpos(self::SPECIAL_CHARS, $char) !== false);
            }

            $passwordReady = ($hasLowercase && $hasUppercase && $hasDigit && $hasSpecialChar);

            // If the new password is valid, check for similarity
            if ($passwordReady) {
                foreach ($diffStrings as $string) {
                    similar_text($password, $string, $similarityPerc);
                    $passwordReady = $passwordReady && ($similarityPerc < $maxSimilarityPerc);
                }
            }
        }
        return $password;
    }
}
