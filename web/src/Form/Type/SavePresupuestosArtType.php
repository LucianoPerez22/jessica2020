<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class SavePresupuestosArtType extends AbstractType
{
    private $_additionalName;
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->_additionalName = $options['num_control'];

        $builder
            ->add('cant' . $this->_additionalName, NumberType::class, ['label'  => false, 'data'  => 1,])
            ->add('idArt' . $this->_additionalName, EntityType::class, [ 
                'class'         => 'App:Articulos',
                'required'      => true, 
                'label'         => false,                      
                'choice_label'  => 'descripcion',    
                'placeholder'   => 'Seleccione un Articulo',               
                'attr'          => ['class' => 'form-control js-select2'],
            ])                            
            ->add('precio' . $this->_additionalName, NumberType::class, ['label' => false, 'data' => 0])
            ->add('total' . $this->_additionalName, NumberType::class, ['label'  => false, 'data' => 0, 'attr'=> [ 'readonly' => true ]])
            ->add('delete'. $this->_additionalName, ButtonType::class, [
                'label' => 'X',
                'attr' =>  ['class' => 'clickeable btn btn-danger']
            ]);
            ;      
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'num_control' => 0,
        ]);

        $resolver->setRequired(['num_control']);
    }

    public function getBlockPrefix()
    {
        return "art" . $this->_additionalName ;
    }
}