<?php
namespace Helper;


class Error
{
    public static $errors;

    /**
     * Set an error inside the class's own error container attribute.
     * @param string $errorName - the error name
     * @param string $errorMessage - the message to link to the error.
     */
    public static function setError($errorName, $errorMessage)
    {
        self::$errors[$errorName] = $errorMessage;
    }

    /**
     * Search in class's errors attribute for a specific error and get message if it exists.
     * @param string $errorName - name of the error
     * @return string|bool - string/error message if error exists, else returns false
     */
    public static function error($errorName)
    {
        return isset(self::$errors[$errorName]) ? self::$errors[$errorName] : false;
    }

    /**
     * Get all errors.
     * @return array - combination errors name - message.
     */
    public static function getErrors()
    {
        return self::$errors;
    }
}