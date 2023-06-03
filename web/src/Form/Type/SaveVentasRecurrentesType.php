<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            /*
            ->add('hasta', DateType::class, [
                'widget' => 'single_text',
                'constraints' => array(
                    new NotNull()
                ),
            ])
            */
            ->add('myfile', FileType::class, [
                'label' => 'Select Document',
                'mapped'    => false, // Tell that there is no Entity to link
                'required'  => true,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
