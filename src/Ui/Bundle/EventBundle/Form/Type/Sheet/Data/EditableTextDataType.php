<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\Object\EditableText;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditableTextDataType extends AbstractType
{
    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @param TranslatorAdapter $translator
     */
    public function __construct(TranslatorAdapter $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $object    = $options['object'];
        $locale    = $options['locale'];
        $attributes = [
            'rows' => 7,
        ];

        if (null !== $object->getOption('maxLength') && '' !== $object->getOption('maxLength')) {
            $attributes['data-text-max-length-indicator']    = $object->getOption('maxLength');
            $attributes['data-text-max-length-translations'] = sprintf(
                '%s|%s|%s',
                $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.plural', [], 'forms', $locale),
                $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.singular', [], 'forms', $locale),
                $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.reached', [], 'forms', $locale)
            );
        }

        $builder
            ->add('content', TextareaType::class, [
                'placeholder' => $options['placeholder'],
                'label'       => false,
                'attr'        => $attributes,
                'required'    => $object->getRequired(),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => EditableText::class,
            'placeholder' => null,
            'help'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_editable_text_data';
    }
}
