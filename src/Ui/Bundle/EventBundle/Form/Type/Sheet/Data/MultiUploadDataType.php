<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultiUploadDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MultiUploadCollectionObject $uploadCollection */
        $uploadCollection = $options['object'];

        $builder
            ->add('uploads', CollectionType::class, [
                'entry_type' => UploadDataType::class,
                'entry_options' => [
                    'label' => false,
                    'collection' => $options['data'],
                    'required' => false,
                    'titlePlaceholder' => $uploadCollection->getTitlePlaceholder(),
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'max' => $uploadCollection->getMax(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => MultiUploadCollectionObject::class,
            'placeholder' => null,
            'help' => null,
            'attr' => [
                'data-product-selector' => (int) true,
            ],
        ]);
    }
}
