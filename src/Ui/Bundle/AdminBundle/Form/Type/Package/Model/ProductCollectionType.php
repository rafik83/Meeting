<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\DataTransformer\RankedCollectionTransformer;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new RankedCollectionTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'product_types', 'collection_group']);
        $resolver->setDefaults([
            'entry_type'     => RankedType::class,
            'allow_add'      => true,
            'allow_delete'   => true,
            'prototype_name' => '__option__',
            'entry_options'  => function (Options $options) {
                return [
                    'rank_options' => [
                        'attr' => [
                            'data-rank' => $options['collection_group'],
                        ],
                    ],
                    'item_type'    => ProductChoiceType::class,
                    'item_options' => [
                        'label'            => false,
                        'event'            => $options['event'],
                        'locale'           => $options['locale'],
                        'placeholder'      => '',
                        'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                            return $productRepository->findByEventAndTypes($options['event'], $options['product_types']);
                        },
                        'attr'             => [
                            'data-shared-choices-collection-item' => $options['collection_group'],
                        ],
                    ],
                    'error_mapping' => [
                        '.'  => 'item',
                    ],
                ];
            },
            'attr'             => function (Options $options) {
                return [
                    'data-shared-choices-collection' => $options['collection_group'],
                    'data-sortable-collection'       => $options['collection_group'],
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
}
