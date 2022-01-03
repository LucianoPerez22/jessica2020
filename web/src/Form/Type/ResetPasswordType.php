<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use App\Form\Model\ResetPasswordModel;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedType::class, array('type' => PasswordType::class,
                                                    'required' => true,
                                                    'invalid_message' => 'Passwords does not match.',
                                                    'first_options'  => array('label' => 'form.labels.new_password',
                                                        'label_attr' => array('class' => 'controlLabel') ),
                                                    'second_options' => array('label' => 'form.labels.repeat_password',
                                                        'label_attr' => array('class' => 'controlLabel')),
                                                    'data' => ''));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => ResetPasswordModel::class,
        ));
    }
}
