<?php

namespace App\Form\Type;

use App\Entity\ListaDeUsuarios;
use App\Entity\Ventas;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                // 'query_builder' => function (EntityRepository $er) {
                //     return $er->createQueryBuilder('m')->orderBy('m.descripcion');
                // },
                'attr'          => ['class' => 'js-select2'],    
                'empty_data' => null,      
            ])    
            // ->add('codigo', TextType::class, [])
            // ->add('descripcion', TextType::class, [])
            // ->add('precio', NumberType::class, [])
            // ->add('ganancia', NumberType::class, [])            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ventas::class,
        ]);
    }
}
