<?php
namespace Helper;


class Session
{
    /**
     * Errors held in the session
     * @var array
     */
    protected static $errors = [];

    /**
     * Get all errors persisted in Session
     * @return array - associative array -> the combination name - message for all persisted errors
     */
    public static function getErrors()
    {
        if (empty(self::$errors))
        {
            return self::initErrors();
        }
        return self::$errors;
    }

    /**
     * Init errors attribute with Session's persisted errors
     * @return array
     */
    public static function initErrors()
    {
        return self::setErrors(isset($_SESSION['errors']) ? $_SESSION['errors'] : []);
    }

    /**
     * Method to set multiple errors at the same time.
     * @param array $errors - assoc array name - message for specific error
     * @return array - array of errors (name and message)
     */
    public static function setErrors($errors)
    {
        self::$errors = $errors;
        self::persistErrors();
        return self::getErrors();
    }

    /**
     * @param string $name - name of the error -> key in array
     * @param string $message - Descriptive message of the error
     */
    public static function setError($name, $message)
    {
        self::getErrors();
        self::$errors[$name] = $message;
        self::persistErrors();
    }

    /**
     * Get an error from the Session's errors
     * @param $name
     * @return bool|string -> false if error doesn't exist, message as a string if it does exist
     */
    public static function getError($name)
    {
        $errors = self::getErrors();
        return isset($errors[$name]) ? $errors['name'] : false;
    }

    /**
     * Saves current errors registered in Session class to the Browser's session
     */
    public static function persistErrors()
    {
        $_SESSION['errors'] = self::getErrors();
    }

    /**
     * Unset errors from Browser's Session
     */
    public static function cleanErrors()
    {
        unset($_SESSION['errors']);
    }
}