<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Application\Query\Catalog\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacetViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    const FILTER_ORGANIZATION_CATEGORY = 'organizationCategory';
    const FILTER_LOCALIZATION          = 'localization';
    const FILTER_POSITION              = 'position';
    const FILTER_TYPE                  = 'type';
    const FILTER_CONTENT               = 'content';
    const ORDER_BY                     = 'orderBy';

    /**
     * @var SearchFacetViewQueryHandler
     */
    private $searchFacetViewQueryHandler;

    /**
     * SearchType constructor.
     *
     * @param SearchFacetViewQueryHandler $searchFacetViewQueryHandler
     */
    public function __construct(SearchFacetViewQueryHandler $searchFacetViewQueryHandler)
    {
        $this->searchFacetViewQueryHandler = $searchFacetViewQueryHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeViews                 = $options['typeViews'];
        $organizationCategoryViews = $options['organizationCategoryViews'];
        $positionViews             = $options['positionViews'];

        $searchFacetsView = $this->searchFacetViewQueryHandler->handle(
            new SearchFacetViewQuery($options['event'], $options['locale'])
        );

        $builder
            ->add(self::ORDER_BY, ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.relevance'          => Constant::ORDER_BY_RELEVANCE,
                    'form.search.orderBy.alphabetical'       => Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.dateAddedToCatalog' => Constant::ORDER_BY_DATE_ADDED_TO_CATALOG,
                ],
            ]);

        // show type facette only if there is more than one filter
        if (count($typeViews) > 1 && $searchFacetsView->hasType() !== null) {
            $builder
                ->add(
                    self::FILTER_TYPE,
                    ChoiceType::class,
                    [
                        'label'        => 'form.search.type.label',
                        'expanded'     => true,
                        'multiple'     => true,
                        'choices'      => $typeViews,
                        'choice_value' => function (TypeView $typeView) {
                            return $typeView->id;
                        },
                        'choice_label' => function (TypeView $typeView) {
                            return $typeView->title;
                        },
                    ]
                );
        }

        if ($organizationCategoryFacet = $searchFacetsView->hasOrganizationCategory()) {
            $builder->add(self::FILTER_ORGANIZATION_CATEGORY, ChoiceType::class, [
                'label'        => $organizationCategoryFacet->label,
                'choices'      => $organizationCategoryViews,
                'choice_value' => function (OrganizationCategoryView $organizationCategoryView = null) {
                    if ($organizationCategoryView !== null) {
                        return $organizationCategoryView->key;
                    }

                    return null;
                },
                'choice_label' => function (OrganizationCategoryView $organizationCategoryView = null) {
                    if ($organizationCategoryView !== null) {
                        return $organizationCategoryView->title;
                    }

                    return null;
                },
                'required'     => false,
                'multiple'     => true,
                'attr'         => [
                    'class'               => 'form-control select2',
                    'data-disallow-clear' => 'true',
                ],
            ]);
        }

        if ($localizationFacet = $searchFacetsView->hasLocalization()) {
            $builder->add(self::FILTER_LOCALIZATION, HiddenType::class, [
                'label'    => $localizationFacet->label,
                'required' => false,
            ]);
        }

        if ($keywordFacet = $searchFacetsView->hasKeywords()) {
            $builder->add(self::FILTER_CONTENT, HiddenType::class, [
                'label' => $keywordFacet->label,
            ]);
        }

        if ($positionFacet = $searchFacetsView->hasPosition()) {
            $builder->add(self::FILTER_POSITION, TagChoiceType::class, [
                'label'   => $positionFacet->label,
                'choices' => $positionViews,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'typeViews',
            'organizationCategoryViews',
            'positionViews',
            'event',
            'locale',
        ]);

        $resolver->setAllowedTypes('event', Event::class);

        $resolver->setDefaults([
            'required'        => false,
            'method'          => 'GET',
            'csrf_protection' => false,
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
