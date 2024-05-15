<?php

namespace App\Form\Type;

use App\Entity\ListaDeUsuarios;
use App\Entity\Ventas;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SaveVentasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idCliente', EntityType::class, [
                'class'         => ListaDeUsuarios::class,
                'required'      => true,
                'label'         => 'Cliente',
                'choice_label'  => 'Nombre',
                'placeholder'   => 'Seleccione un Cliente',
                'attr'          => ['class' => 'form-control js-select2'],
            ])
            ->add('formaPago', ChoiceType::class, [
                'choices' => [
                    'Efectivo'      => 'Efectivo',
                    'Tarjeta'       => 'Tarjeta' ,
                    'Transferencia' => 'Transferencia',
                ],
                'attr'          => ['class' => 'form-control js-select2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ventas::class,
        ]);
    }
}
