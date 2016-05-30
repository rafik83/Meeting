<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'product_types', 'collection_group']);
        $resolver->setDefaults([
            'entry_type'     => ProductChoiceType::class,
            'allow_add'      => true,
            'allow_delete'   => true,
            'prototype_name' => '__option__',
            'entry_options'  => function (Options $options) {
                return [
                    'label'            => false,
                    'event'            => $options['event'],
                    'placeholder'      => '',
                    'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                        return $productRepository->findByEventAndTypes($options['event'], $options['product_types']);
                    },
                    'attr'             => [
                        'data-shared-choices-collection-item' => $options['collection_group'],
                    ],
                ];
            },
            'attr'             => function (Options $options) {
                return [
                    'data-shared-choices-collection' => $options['collection_group'],
                    'data-sortable'                  => '',
                ];
            },
       ]);
    }

    public function getBlockPrefix()
    {
        return 'product_collection';
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
