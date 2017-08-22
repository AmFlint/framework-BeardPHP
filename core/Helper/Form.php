<?php

namespace Helper;

class Form extends Model
{
    public $email = 'lol';
    public $masselot;
    public function rules()
    {
        return [
            ['masselot' => ['test']],
            ['email' =>  ['testEmail', 'when' => true]],
            ['email' => ['max:5', 'min:2']],
        ];
    }

    public function messages()
    {
        return [];
    }

    public function validate()
    {
        $rules = $this->rules();
        foreach ($rules as $rule)
        {
            if (!is_string(key($rule)))
            {
                throw new \Exception('Attribute to validate must be a string');
            }

            $attribute = key($rule);
            $currentRule = current($rule);

            if (
                empty($this->$attribute)
                && !isset($currentRule['skipIfEmpty'])
                ||
                (isset($currentRule['skipIfEmpty'])
                && $currentRule['skipIfEmpty'] === false)
            )
            {
                continue;
            }

            // if conditional validation is used and condition is false go to the next rule
            // if executing validation on unknown attribute, go to next validation rule
            if (
                isset($currentRule['when']) && !$currentRule['when']
                || !property_exists($this, key($rule))
            )
            {
                continue;
            }
            // unset condition for the next loop
            unset($currentRule['when']);
            // now looping on attribute's multiple validations
            foreach ($currentRule as $method)
            {
                if (!is_string($method))
                {
                    throw new \Exception('Validation Rule must be a string.');
                }
                // if string possesses ':' (framework's native validation rules)
                // used to pass a specific parameter to a method
                if (strstr($method, ':'))
                {
                    $method = explode(':', $method);
                    if (!method_exists($this, $method[0]))
                    {
                        throw new \Exception('Validation Method does not exist');
                    }
                    $methodName = $method[0];
                    $validated = $this->$methodName($attribute, $method[1]);
                    dump($validated);
                }
            }
        }
    }

    public function testEmail($attribute)
    {
        var_dump($this->$attribute);
    }

    /**
     * Validation method for minimum string length or value if attribute is a number
     * @param string|number $attribute
     * @param number $minimum - The minimum you want the form value to be.
     * @return bool - true if validation passed else false (called on array or fail)
     */
    protected function min($attribute, $minimum)
    {
        $formAttribute = $this->$attribute;
        if (
            (is_numeric($formAttribute) && $formAttribute >= $minimum)
            ||
            (is_string($formAttribute) && strlen($formAttribute) >= $minimum)
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
        $formAttribute = $this->$attribute;
        if (
            (is_numeric($formAttribute) && $formAttribute <= $maximum)
            ||
            (is_string($formAttribute) && strlen($formAttribute) <= $maximum)
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
