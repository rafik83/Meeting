<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet\AbstractSearchFacetType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet\TranslationType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchFacetType extends AbstractSearchFacetType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label'         => true,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type'    => TranslationType::class,
                'label'         => false,
            ]);
    }
}
