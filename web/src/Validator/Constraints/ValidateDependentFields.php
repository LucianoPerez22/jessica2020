<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidateDependentFields extends Constraint
{
    public $message = '';
    public $field = null;
    public $dependentField = null;
    public $dependentValues = null;
    public $relationMethod = null;
    public $errorPath = null;

    public function __construct($options = null)
    {
        $this->field = $options['field'];
        $this->dependentField = $options['dependentField'];
        $this->relationMethod = (isset($options['relationMethod'])) ? $options['relationMethod'] : null;
        $this->dependentValues = (isset($options['dependentValues'])) ? $options['dependentValues'] : array();
        $this->message = (isset($options['message'])) ? $options['message'] : 'This value must not be empty';
        $this->errorPath = (isset($options['errorPath'])) ? $options['errorPath'] : null;
    }
    
    public function getRequiredOptions()
    {
        return array('field','dependentField');
    }

    public function validatedBy()
    {
        return 'app_validate_dependent_fields';
    }

    public function getTargets()
    {
        //le digo que es una constraint de clase
        return self::CLASS_CONSTRAINT;
    }
}
