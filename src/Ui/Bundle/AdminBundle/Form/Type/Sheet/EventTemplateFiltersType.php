<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\BooleanFiltersBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choice type that extracts choices from an event's registration template (template's boolean fields marked as "filter")
 */
class EventTemplateFiltersType extends AbstractType
{
    /**
     * @var BooleanFiltersBuilder
     */
    private $booleanFilterBuilder;

    /**
     * @param BooleanFiltersBuilder $booleanFiltersBuilder
     */
    public function __construct(BooleanFiltersBuilder $booleanFiltersBuilder)
    {
        $this->booleanFilterBuilder = $booleanFiltersBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                $filters = $this->booleanFilterBuilder->getFilters($options['event']);

                return array_flip($filters);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_filters_from_template';
    }
}
