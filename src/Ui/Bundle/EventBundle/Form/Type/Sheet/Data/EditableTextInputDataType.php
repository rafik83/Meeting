<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\Object;
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
        $label  = $options['label'];
        $attr   = $text->getOption('maxLength') ? ['maxlength' => $text->getOption('maxLength')] : [];

        $builder
            ->add('content', TextType::class, [
                'label'       => $label ? $text->getOption('label')[$locale] : false,
                'required'    => $text->getOption('required'),
                'placeholder' => $text->getOption('placeholder')[$locale],
                'attr'        => $attr,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', Object\EditableText::class);
        $resolver->setDefaults([
            'label'      => false,
            'data_class' => Object\EditableText::class
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
