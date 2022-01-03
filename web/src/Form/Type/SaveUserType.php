<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\Type\Custom\CustomSingleCheckbox;
use App\Entity\User;

class SaveUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label'    => 'form.labels.name',
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label'    => 'form.labels.lastName',
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'label'    => 'form.labels.username',
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'label'    => 'form.labels.email',
            ])
            ->add('enabled', CustomSingleCheckbox::class, [
                'required'     => false,
                'label'        => 'form.labels.status',
                'true_choice'  => 'form.choices.enabled',
                'false_choice' => 'form.choices.disabled',
            ])
            ->add('groups', EntityType::class, [
                'class'        => 'App:Group', 'required' => false,
                'label'        => 'form.labels.groups',
                'choice_label' => 'name',
                'attr'         => ['class' => 'controlGroup'],
                'expanded'     => true,
                'multiple'     => true,
            ]);

        if ($options['admin']) {
            $builder->add('superAdmin', CustomSingleCheckbox::class, ['required' => false, 'label' => 'form.labels.admin']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => User::class,
            'edit'              => false,
            'admin'             => false
        ]);
    }
}
