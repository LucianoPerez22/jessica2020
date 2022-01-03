<?php

namespace App\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ValidateAssociatedDates
{
    public static function validate($object, ExecutionContextInterface $context, $payload)
    {
        if(is_null($object->getEts()) && is_null($object->getAts())){
            return ;
        }

        if(!is_null($object->getEta()) && $object->getEta() > $object->getEts()){
            $context->buildViolation('custom.validator.start_date_ets')
                ->atPath('eta')
                ->addViolation()
            ;
        }

        if(!is_null($object->getAta()) && $object->getAta() > $object->getAts()){
            $context->buildViolation('custom.validator.start_date_ats')
                ->atPath('ata')
                ->addViolation()
            ;
        }
    }

}