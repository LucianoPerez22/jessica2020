<?php

namespace App\Form\Type\Custom;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\Type\Custom\DateTimeW3CType;


class CustomFromAndToDateType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translatedInputName = $this->translator->trans($options['input_name']);
        
        $builder
            ->add('from', DateTimeW3CType::class, [
                'label'                => $translatedInputName . " " . $this->translator->trans('form.labels.from'),
                'widget'               => 'single_text',
                'label_format'         => $options['view_format'],
                'attr'                 => [
                    'class' => 'fieldInput sizeDatetimepicker',
                ]
            ])
            ->add('to', DateTimeW3CType::class, [
                'label'                => $translatedInputName . " " . $this->translator->trans('form.labels.to'),
                'widget'               => 'single_text',
                'label_format'          => $options['view_format'],
                'attr'                 => [
                    'class' => 'fieldInput sizeDatetimepicker',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['view_format', 'validation_format', 'input_name']);
    }
}
