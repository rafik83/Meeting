<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Application\Command\Package\Model\Group;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('labels', TranslationsType::class, [
                'entry_type' => TextType::class,
                'locales'    => $options['event']->getLocales(),
                'required'   => false,
            ])
            ->add('options', CollectionType::class, [
                'entry_type'     => ProductChoiceType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'prototype_name' => '__option__',
                'entry_options'  => [
                    'label'            => false,
                    'event'            => $options['event'],
                    'placeholder'      => '',
                    'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                        return $productRepository->findByEventAndTypes($options['event'], [Product::TYPE_OPTION]);
                    },
                    'attr'             => [
                        'data-shared-choices' => 'options'
                    ],
                ],
                'attr'             => [
                    'data-shared-choices-collection' => 'options'
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}
