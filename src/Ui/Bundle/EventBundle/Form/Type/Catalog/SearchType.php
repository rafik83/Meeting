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
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

        $builder->add(self::FILTER_OBJECTIVE, ChoiceType::class, [
            'label'    => 'form.search.objective.label',
            'expanded' => true,
            'multiple' => true,
            'choices'  => [
                'form.search.objective.supply' => Nomenclature::OBJECTIVE_SUPPLY,
                'form.search.objective.need'   => Nomenclature::OBJECTIVE_NEED,
            ],
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
