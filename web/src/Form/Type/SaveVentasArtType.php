<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class SaveVentasArtType extends AbstractType
{
    private $_additionalName;

    public function __construct($additionalName= ''){
        $this->_additionalName = $additionalName;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {       
        $this->_additionalName = $options['algo'][0];                
        $builder
            ->add('cant' . $options['algo'][0], NumberType::class, ['label'  => false, 'data'  => 1,])
            ->add('idArt' . $options['algo'][0], EntityType::class, [ 
                'class'         => 'App:Articulos',
                'required'      => true, 
                'label'         => false,                      
                'choice_label'  => 'descripcion',    
                'placeholder'   => 'Seleccione un Articulo',               
                'attr'          => ['class' => 'js-select2'],    
                'empty_data'    => null,                 
                'query_builder' => function (EntityRepository $er) {                                            
                      return $er->createQueryBuilder('a')  
                             ->select('a')                         
                             ->addSelect('sum(s.cantidad) HIDDEN cant')
                             ->join('a.stock', 's' )
                             ->having('cant > 0')
                             ->groupBy("a.id")                            
                             ->orderBy('cant', 'DESC')
                             ;                                                                                                         
                    },                                
            ])                            
            ->add('precio' . $this->_additionalName, NumberType::class, ['label' => false, 'data' => 0, 'attr'=> [ 'readonly' => true ]])
            ->add('total' . $this->_additionalName, NumberType::class, ['label'  => false, 'data' => 0, 'attr'=> [ 'readonly' => true ]])
            ->add('delete'. $options['algo'][0], ButtonType::class, [
                'label' => 'X',
                'attr' =>  ['class' => 'clickeable btn btn-danger']
            ]);
            ;      
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'algo' => null,            
        ]);
    }

    public function getBlockPrefix()
    {
        return "art" . $this->_additionalName ;
    }
}
