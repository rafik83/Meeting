<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\MimeType\MimeType;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class FileDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TemplateObject\UploadObject $file */
        $file = $options['object'];

        $mimeTypes = MimeType::getMimeTypesByFormats($file->getOption('formats'));
        if (!$mimeTypes) {
            return;
        }

        $builder->add('file', FileType::class, [
            'label' => $options['showLabel'] === true ? $file->getLabel($options['locale']) : false,
            'required' => $file->getOption('required'),
            'attr' => [
                'accept' => implode(', ', $mimeTypes),
            ],
            'constraints' => [
                new File([
                    'mimeTypes' => $mimeTypes,
                    'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['object', 'locale'])
            ->setAllowedTypes('object', TemplateObject\UploadObject::class)
            ->setDefaults([
                'label' => false,
                'showLabel' => false,
                'data_class' => TemplateObject\UploadObject::class,
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sheet_file_data';
    }
}
