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
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\BooleanFiltersBuilder;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\ImportedChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilterType extends AbstractType
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var BooleanFiltersBuilder */
    protected $booleanFilterBuilder;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param BooleanFiltersBuilder       $booleanFilterBuilder
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        BooleanFiltersBuilder $booleanFilterBuilder
    ) {
        $this->categoryRepository   = $categoryRepository;
        $this->booleanFilterBuilder = $booleanFilterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('text', SheetTextSearchType::class, [
                'label' => 'form.sheet_filter.children.text_search.label',
                'attr'  => [
                    'placeholder' => 'form.sheet_filter.children.text_search.label',
                ],
            ])
            ->add('validationState', ValidationStateChoiceType::class, [
                'label'    => 'form.sheet_filter.children.validationState.label',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('state', StateChoiceType::class, [
                'label'    => 'form.sheet_filter.children.state.label',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('inCatalog', YesNoType::class, [
                'label'    => 'form.sheet_filter.children.inCatalog.label',
                'required' => false,
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('completed', CompletedChoiceType::class, [
                'label'    => 'form.sheet_filter.children.completed.label',
                'required' => false,
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('type', TypeChoiceType::class, [
                'label'    => 'form.sheet_filter.children.type.label',
                'event'    => $event,
                'locale'   => $options['locale'],
                'user'     => $options['user'],
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('hasHappeningParticipation', YesNoType::class, [
                'label'    => 'form.sheet_filter.children.hasHappeningParticipation.label',
                'required' => false,
            ])
            ->add('hasNoMeetingRequest', CheckboxType::class, [
                'label'    => 'form.sheet_filter.children.hasNoMeetingRequest.label',
                'required' => false,
            ])
            ->add('hasPendingMeetingPropositions', CheckboxType::class, [
                'label'    => 'form.sheet_filter.children.hasPendingMeetingPropositions.label',
                'required' => false,
            ])
            ->add('imported', ImportedChoiceType::class, [
                'label'    => 'form.sheet_filter.children.imported.label',
                'required' => false,
                'expanded' => true
            ])
            ->add(Constant::HAS_ORDER, ChoiceType::class, [
                'choices'     => [
                    'form.sheet_filter.children.order.yes.label' => true,
                    'form.sheet_filter.children.order.no.label'  => false,
                ],
                'multiple'    => false,
                'expanded'    => true,
                'label'       => 'form.sheet_filter.children.hasOrder.label',
                'required'    => false,
                'placeholder' => 'form.sheet_filter.children.order.noPreference.label',
            ])
            ->add(Constant::HAS_CART, ChoiceType::class, [
                'choices'     => [
                    'form.sheet_filter.children.cart.yes.label' => true,
                    'form.sheet_filter.children.cart.no.label'  => false,
                ],
                'required'    => false,
                'multiple'    => false,
                'expanded'    => true,
                'label'       => 'form.sheet_filter.children.hasCart.label',
                'placeholder' => 'form.sheet_filter.children.cart.noPreference.label',
            ])
            ->add(Constant::HAS_REMAINING_TO_PAY, ChoiceType::class, [
                'choices'     => [
                    'form.sheet_filter.children.hasRemainingToPay.yes.label' => true,
                    'form.sheet_filter.children.hasRemainingToPay.no.label'  => false,
                ],
                'placeholder' => 'form.sheet_filter.children.hasRemainingToPay.noPreference.label',
                'label'       => 'form.sheet_filter.children.hasRemainingToPay.label',
                'multiple'    => false,
                'expanded'    => true,
                'required'    => false,
            ]);

        $builder
            ->add('follower', FollowerChoiceType::class, [
                'label'      => 'form.sheet_filter.children.follower.label',
                'event'      => $event,
                'unassigned' => false,
                'required'   => false,
                'multiple'   => true,
                'expanded'   => true,
            ])
            ->add('registeredAt', CreationIntervalFilterType::class, [
                'label'                     => 'form.sheet_filter.children.creation_interval.label',
                'required'                  => false,
                'choice_translation_domain' => 'messages',
                'expanded'                  => true,
                'multiple'                  => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'user']);
        $resolver->setAllowedTypes('event', Event::class);
    }
}
