<?php

namespace App\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = ['form.choices.all' => "", 'form.choices.enabled' => "1", 'form.choices.disabled' => "0"];
        $builder
            ->add('name', TextType::class, ['label' => 'form.labels.name'])
            ->add('username', TextType::class, ['label' => 'form.labels.username'])
            ->add('email', TextType::class, ['label' => 'form.labels.email'])
           ;

        $builder ->add('enabled', ChoiceType::class, [ 'label' => 'form.labels.status','choices' => $choices]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        //seteo el dominio de la traduccion y que no tenga proteccion csrf
        $resolver->setDefaults([
            'csrf_protection' => false,
            'required'        => false,
            'admin'           => false,
        ]);
    }
}
