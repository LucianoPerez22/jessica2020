<?php

namespace App\Form\Type;

use App\Entity\ListaDeUsuarios;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SaveClienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [])
            ->add('direccion', TextType::class, [])
            ->add('telefono', TextType::class, [])
            ->add('tipoIva', ChoiceType::class, [
                'choices' =>[
                    'Consumidor Final' => 'final',
                    'Responsable Inscripto' => 'responsable',
                    'Monotributista' => 'monotributo',
                    'Exento' => 'exento',

                ]
            ])
            ->add('documento', TextType::class, [])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ListaDeUsuarios::class,
        ]);
    }
}
