<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetFilterType extends AbstractFilterType
{
    /**
     * @return array
     */
    public static function getDefaultFilters()
    {
        return [
            'enabled' => true,
            'orderBy' => Constant::ORDER_BY_CREATED_AT,
            self::getOrderStatusDefaultFilters()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('enabled', EnabledStateChoiceType::class, [
                'label'    => 'form.sheet_filter.children.enabledState.label',
                'multiple' => false,
                'expanded' => true,
                'required' => false,
            ])
            ->add('orderBy', SortChoiceType::class, [
                'label' => 'form.sheet_filter.children.orderBy.label',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['event', 'locale', 'user']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_filter';
    }

    public static function getOrderStatusDefaultFilters(): array
    {
        return [
            'order_status' => [
                Constant::ORDER_STATUS_NO_ORDER,
                Constant::ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO,
                Constant::ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO,
            ]
        ];
    }
}
