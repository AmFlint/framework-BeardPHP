<?php

namespace Helper;
/**
 * A Model Representation in charge of validating User Inputs and managing error/success messages
 * Class Form
 * @package Helper
 */
class Form extends Model
{
    protected $validated = true;
    public $email = 'lolee';
    public $age = '10';
    public function rules()
    {
        return [
            ['age' => ['integer']],
            ['email' =>  ['testEmail', 'when' => false]],
            ['email' => ['max:5', 'min:2']],
        ];
    }

    public function messages()
    {
        return [];
    }

    /**
     * Executes all the validation methods listed in method rules and stores success
     * and error messages.
     * @return bool
     * @throws \Exception
     */
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
                    $argument = $method[1];
                    $method = $method[0];
                    $validated = $this->$method($attribute, $argument);
                }
                else // Calling for custom method
                {
                    if (!method_exists($this, $method))
                    {
                        throw new \Exception('Validation method does not exist');
                    }
                    $validated = $this->$method($attribute);
                }

                if (!$validated && $this->validated)
                {
                    $this->setValidated(false);
                }

                $this->setMessage($attribute, $method, $validated);
            }
        }
        // return validation state of the form so the controller manages treatment
        return $this->isValidated();
    }

    /**
     * Check if an attribute is an integer
     * @param $attribute - form attribute to check
     * @return bool - true if attribute is an integer, otherwise false
     */
    public function integer($attribute)
    {
        return is_numeric($this->$attribute);
    }

    public function testEmail($attribute)
    {
        if ($this->$attribute === 'lol')
        {
            return true;
        }
        return false;
    }

    /**
     * Getter for form's `validated` attribute
     * @return bool
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * Set the `validated` attribute for current Form
     * @param bool $validated
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
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

    /**
     * Save an error Name/Message
     * @param string $attribute - Attribute name
     * @param string $method - Method Name
     * @param bool - $validation - state of validation for current method on current attribute
     */
    public function setMessage($attribute, $method, $validation)
    {
        $messages = $this->messages();
        $key = "${attribute}.${method}";
        // If message is set and the rule validation failed
        // TODO : allow success messages
        if (isset($messages[$key]) && !$validation)
        {
            Session::setError($key, $messages[$key]);
        }
    }
}
