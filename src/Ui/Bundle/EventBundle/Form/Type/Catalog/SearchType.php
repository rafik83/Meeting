<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractSearchType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(SearchFields::ORDER_BY, ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.relevance'          => Constant::ORDER_BY_RELEVANCE,
                    'form.search.orderBy.alphabetical'       => Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.dateAddedToCatalog' => Constant::ORDER_BY_DATE_ADDED_TO_CATALOG,
                ],
            ])
            ->add(SearchFields::FILTER_OBJECTIVE, ChoiceType::class, [
                'label'    => 'form.search.objective.label',
                'expanded' => true,
                'multiple' => true,
                'choices'  => [
                    'form.search.objective.supply' => Nomenclature::OBJECTIVE_SUPPLY,
                    'form.search.objective.need'   => Nomenclature::OBJECTIVE_NEED,
                ],
            ])
        ;

        if ($options['filterByAvailableSlotIds'] === true) {
            $everyone = CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_EVERYONE;
            $available = CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_AVAILABLE;
            $builder
                ->add(SearchFields::FILTER_AVAILABLE_SLOT_IDS, ChoiceType::class, [
                    'choices' => [
                        sprintf('form.search.availableSlot.choice.%s', $everyone) => $everyone,
                        sprintf('form.search.availableSlot.choice.%s', $available) => $available,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'label'    => 'form.search.availableSlot.label'
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'filterByAvailableSlotIds' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_search';
    }
}
