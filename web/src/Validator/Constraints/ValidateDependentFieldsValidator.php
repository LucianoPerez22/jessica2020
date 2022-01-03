<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ValidateDependentFieldsValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (null == $constraint->field && !is_string($constraint->field)) {
            throw new UnexpectedTypeException($constraint->field, 'string');
        }
        
        if (null == $constraint->dependentField && !is_string($constraint->dependentField)) {
            throw new UnexpectedTypeException($constraint->dependentField, 'string');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        if (null !== $constraint->dependentValues && !is_array($constraint->dependentValues)) {
            throw new UnexpectedTypeException($constraint->dependentValues, 'array or null');
        }
       
        $field = (string) $constraint->field;
        $methodField = "get".ucfirst($field);
        $fieldDependent = (string) $constraint->dependentField;
        $methodFieldDependent = "get".ucfirst($fieldDependent);
        $valueField = $entity->$methodField();
        if ($valueField === "") {
            return true;
        }
        
        $dependentValues = $constraint->dependentValues;
        if (null != $constraint->relationMethod && !empty($valueField)) {
            $relationMethod = $constraint->relationMethod;
            $valueField = $valueField->$relationMethod();
        }
   
        if (null !== $dependentValues) {
            $returnValue = true;
            foreach ($dependentValues as $dependentValue) {
                if ($dependentValue == $valueField) {
                    $returnValue = false;
                }
            }
            if ($returnValue) {
                return true;
            }
        }
        
        $valueDependent = $entity->$methodFieldDependent();

        if ($valueDependent instanceof \Doctrine\ORM\PersistentCollection
            ||$valueDependent instanceof \Doctrine\Common\Collections\ArrayCollection) {
            if ($valueDependent->count() > 0) {
                return true;
            }
        } elseif (is_array($valueDependent)) {
            if (count($valueDependent) > 0) {
                return true;
            }
        } elseif ($valueDependent != "") {
            return true;
        }
        
        $errorPath = null !== $constraint->errorPath ?  (string) $constraint->errorPath : (string) $constraint->field;
        $this->context->buildViolation($constraint->message)
                      ->atPath($errorPath)
                      ->setInvalidValue($valueField)
                      ->addViolation();
    }
}
