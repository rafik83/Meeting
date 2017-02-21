<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\CategoryChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetFilterType extends AbstractFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('category', CategoryChoiceType::class, [
                'label'  => 'form.sheet_filter.children.category.label',
                'event'  => $options['event'],
                'locale' => $options['locale'],
            ])
            ->add('enabled', EnabledStateChoiceType::class, [
                'label'    => 'form.sheet_filter.children.enabledState.label',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add(Constant::HAS_ORDER, ChoiceType::class, [
                'choices'  => [
                    'form.sheet_filter.children.order.noPreference.label' => null,
                    'form.sheet_filter.children.order.yes.label'          => true,
                    'form.sheet_filter.children.order.no.label'          => false,
                ],
                'multiple' => false,
                'expanded' => true,
                'label'    => 'form.sheet_filter.children.hasOrder.label',
            ])
            ->add(Constant::HAS_CART, ChoiceType::class, [
                'choices'  => [
                    'form.sheet_filter.children.cart.noPreference.label' => null,
                    'form.sheet_filter.children.cart.yes.label'          => true,
                    'form.sheet_filter.children.cart.no.label'           => false,
                ],
                'multiple' => false,
                'expanded' => true,
                'label'    => 'form.sheet_filter.children.hasCart.label',
            ])
            ->add('hasRemainingToPay', ChoiceType::class, [
                'choices'  => [
                    'form.sheet_filter.children.hasRemainingToPay.noPreference.label' => null,
                    'form.sheet_filter.children.hasRemainingToPay.yes.label'          => true,
                    'form.sheet_filter.children.hasRemainingToPay.no.label'           => false,
                ],
                'label'    => 'form.sheet_filter.children.hasRemainingToPay.label',
                'multiple' => false,
                'expanded' => true,
            ]);

        $booleanFilters = $this->booleanFilterBuilder->getFilters($event);

        if (!empty($booleanFilters)) {
            $builder->add('boolean_filters', ChoiceType::class, [
                'choices'  => array_merge([
                    'form.sheet_filter.children.booleanFilters.noPreference.label' => null,
                ], array_flip($booleanFilters)),
                'label'    => 'form.sheet.filter.children.template_filters.label',
                'multiple' => false,
                'expanded' => true,
            ]);
        }

        $categories = $this->categoryRepository->getCategoriesByEvent($event, $options['locale']);

        if (!empty($categories)) {
            $builder->add('category', CategoryChoiceType::class, [
                'label'    => 'form.sheet_filter.children.category.label',
                'event'    => $event,
                'locale'   => $options['locale'],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'data'     => null,
            ]);
        }
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
        return 'hello';
    }
}
