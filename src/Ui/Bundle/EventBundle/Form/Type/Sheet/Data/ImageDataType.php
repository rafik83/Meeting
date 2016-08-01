<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ImageDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Object\Image $image */
        $image = $options['object'];

        $builder->add('file', FileType::class, [
            'label'       => $options['label'],
            'required'    => $image->getOption('required'),
            'mapped'      => false,
            'attr'        => [
                'accept' => implode(', ', TemplateObject\Image::supportedMimeType()),
            ],
            'constraints' => [
                new Image(['mimeTypes' => TemplateObject\Image::supportedMimeType()]),
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\Image::class);
        $resolver->setDefaults([
            'label'       => false,
            'data_class'  => TemplateObject\Image::class,
            'placeholder' => null,
            'help'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_image_data';
    }
}
