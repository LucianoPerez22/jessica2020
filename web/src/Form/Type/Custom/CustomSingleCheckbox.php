<?php

namespace App\Form\Type\Custom;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CustomSingleCheckbox extends AbstractType
{
    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (array_key_exists('true_choice', $options)) {
            $view->vars['true_choice'] = $options['true_choice'];
        }

        if (array_key_exists('false_choice', $options)) {
            $view->vars['false_choice'] = $options['false_choice'];
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['true_choice', 'false_choice']);
        $defaults = [
            'true_choice'  => 'form.choices.true',
            'false_choice' => 'form.choices.false',
            'expanded'     => false,
            'multiple'     => false,
        ];

        $resolver->setDefaults($defaults);
    }

    public function getParent()
    {
        return CheckboxType::class;
    }
}
