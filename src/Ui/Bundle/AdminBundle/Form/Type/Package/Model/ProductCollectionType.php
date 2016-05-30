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
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCollectionType extends AbstractType implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'product_types', 'collection_group']);
        $resolver->setDefaults([
            'entry_type'     => RankedType::class,
            'allow_add'      => true,
            'allow_delete'   => true,
            'prototype_name' => '__option__',
            'entry_options'  => function (Options $options) {
                return [
                    'value_type'    => ProductChoiceType::class,
                    'value_options' => [
                        'label'            => false,
                        'event'            => $options['event'],
                        'placeholder'      => '',
                        'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                            return $productRepository->findByEventAndTypes($options['event'], $options['product_types']);
                        },
                        'attr'             => [
                            'data-shared-choices-collection-item' => $options['collection_group'],
                        ],
                    ]
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return [];
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('"array" expected, "%s" given.', gettype($value)));
        }

        return array_map(function (Product $product, $rank) {
            return ['value' => $product, 'rank' => $rank];
        }, array_values($value), array_keys($value));
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return [];
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('"array" expected, "%s" given.', gettype($value)));
        }

        usort($value, function (array $one, array $another) {
            return $one['rank'] - $another['rank'];
        });

        return array_map(function (array $ranked) {
            return $ranked['value'];
        }, $value);
    }
}
