<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use App\Form\Model\ChangePasswordModel;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('oldPassword', PasswordType::class, array('required' => true,
                                                       'label' => 'form.labels.current_password',
                                                       'attr' => array('autocomplete' => 'off')))
                ->add('password', RepeatedType::class, array('type' => PasswordType::class,
                                                    'required' => true,
                                                    'first_options'  => array('label' => 'form.labels.new_password'),
                                                    'second_options' => array('label' => 'form.labels.repeat_password'),
                                                    'data' => ''));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => ChangePasswordModel::class,
        ));
    }
}
