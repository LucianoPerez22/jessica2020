<?php

namespace App\Form\Type;

use App\Entity\ListaDeUsuarios;
use App\Entity\Presupuestos;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SavePresupuestosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*->add('cliente', EntityType::class, [
                'class'         => ListaDeUsuarios::class,
                'required'      => true, 
                'label'         => 'Cliente',                               
                'choice_label'  => 'Nombre',    
                'placeholder'   => 'Seleccione un Cliente',        
                'attr'          => ['class' => 'js-select2'],    
                'empty_data' => null,      
            ])                
        ;*/
        ->add('cliente', TextType::class, [

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Presupuestos::class,
        ]);
    }
}