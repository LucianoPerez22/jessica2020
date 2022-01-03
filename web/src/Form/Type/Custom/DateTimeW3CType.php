<?php

namespace App\Form\Type\Custom;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToHtml5LocalDateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeW3CType extends DateTimeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $dateTransformer = new CallbackTransformer(
            function ($datetime) {
                return $datetime;
            },
            function ($string) {
                
                $dateFormat = \DateTime::createFromFormat('d/m/Y H:i', $string);
                
                if($dateFormat instanceof \DateTime)
                {
                    return $dateFormat->format(DateTimeToHtml5LocalDateTimeTransformer::HTML5_FORMAT);
                }

                return "";
            }
        );

        $builder->addViewTransformer($dateTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}