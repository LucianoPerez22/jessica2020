<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotNull;

class SaveVentasRecurrentesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('desde', DateType::class, [
                'widget' => 'single_text',
                'constraints' => array(
                    new NotNull()
                ),
            ])
            ->add('hasta', DateType::class, [
                'widget' => 'single_text',
                'constraints' => array(
                    new NotNull()
                ),
            ])
            ->add('cantidad', NumberType::class, [
                'label' => 'Cantidad',
                //'data' => 0,
                'constraints' => array(
                    new NotNull()
                ),
            ])
            ->add('importe', NumberType::class, [
                'label' => 'Importe',
                //'data' => 0,
                'constraints' => array(
                    new NotNull()
                ),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
