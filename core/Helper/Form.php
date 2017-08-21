<?php

namespace Helper;

class Form extends Model
{
    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    public function validate()
    {
        $array = [
            ['email' => 'test'],
            ['email' => 'masselot']
        ];
        foreach ($array as $email)
        {
            var_dump($email);
        }
    }

    /**
     * Validation method for minimum string length or value if attribute is a number
     * @param string|number $attribute
     * @param number $minimum - The minimum you want the form value to be.
     * @return bool - true if validation passed else false (called on array or fail)
     */
    protected function min($attribute, $minimum)
    {
        if (
            (is_numeric($attribute) && $attribute >= $minimum)
            ||
            (is_string($attribute) && strlen($attribute) >= $minimum)
        )
        {
            return true;
        }
        return false;
    }

    /**
     * Validation method for maximum string length or value if attribute is a number
     * @param string|number $attribute
     * @param number $maximum - The maximum you want the form value not to exceed
     * @return bool - true if validation passed else false (called on array or fail)
     */
    protected function max($attribute, $maximum)
    {
        if (
            (is_numeric($attribute) && $attribute <= $maximum)
            ||
            (is_string($attribute) && strlen($attribute) <= $maximum)
        )
        {
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $attribute
     */
    protected function email($attribute)
    {

    }
}
