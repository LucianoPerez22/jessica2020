<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class SaveVentasArtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $num_control = $options['num_control'];

        $builder
            ->add('cant' . $num_control, NumberType::class, [
                'label' => false,
                'data' => 1,
                'constraints' => [
                    new NotBlank(),
                    new Type('numeric'),
                ],
                'attr' => [
                    'id' => 'form_cant' . $num_control,
                    'class' => 'form-control',
                ],
            ])
            ->add('idArt' . $num_control, EntityType::class, [
                'class' => 'App:Articulos',
                'required' => true,
                'label' => false,
                'choice_label' => 'descripcion',
                'placeholder' => 'Seleccione un Articulo',
                'attr' => [
                    'id' => 'form_idArt' . $num_control,
                    'class' => 'form-control js-select2',
                ],
            ])
            ->add('precio' . $num_control, NumberType::class, [
                'label' => false,
                'data' => 0,
                'constraints' => [
                    new NotBlank(),
                    new Type('numeric'),
                ],
                'attr' => [
                    'id' => 'form_precio' . $num_control,
                    'class' => 'form-control, js-select2',
                ],
            ])
            ->add('total' . $num_control, NumberType::class, [
                'label' => false,
                'data' => 0,
                'attr' => [
                    'id' => 'form_total' . $num_control,
                    'class' => 'form-control',
                    'readonly' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'num_control' => 0,
        ]);

        $resolver->setRequired(['num_control']);
    }
}