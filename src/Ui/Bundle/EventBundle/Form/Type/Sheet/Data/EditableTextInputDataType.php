<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditableTextInputDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $text   = $options['object'];
        $locale = $options['locale'];
        $attr   = $text->getOption('maxLength') ? ['maxlength' => $text->getOption('maxLength')] : [];

        $builder
            ->add('content', TextType::class, [
                'label'              => $text->getOption('label', $locale),
                'required'           => $text->getOption('required'),
                'placeholder'        => $text->getOption('placeholder')[$locale],
                'attr'               => $attr,
                'translation_domain' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\EditableText::class);
        $resolver->setDefaults([
            'data_class' => TemplateObject\EditableText::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'text_data';
    }
}
