<?php

namespace App\Form\Type\Custom;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomGroupedChoicesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = array('choice_label' => 'title',
                          'expanded' => true,
                          'multiple' => true,
                          'group_by' => 'module'
        );

        $resolver->setDefaults($defaults);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
