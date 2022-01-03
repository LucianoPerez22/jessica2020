<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\Type\Custom\CustomGroupedChoicesType;
use App\Entity\Group;

class SaveGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array( 'label' => 'form.labels.name'))
                ->add('description', TextareaType::class, array('label' => 'form.labels.description', 'attr' => array('rows' => 3),
                                                                           'required' => true))
                ->add(
                    'roles',
                    CustomGroupedChoicesType::class,
                    array('class' => 'App:Role',
                                                    'label'              => 'form.labels.role',
                                                    'choice_label'       => 'title',
                                                    'expanded'           => true,
                                                    'multiple'           => true,
                                                    'group_by'           => 'module'
                                                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => Group::class,
        ));
    }
}
