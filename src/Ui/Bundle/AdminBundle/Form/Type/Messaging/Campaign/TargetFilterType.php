<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\BooleanFiltersBuilder;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\CategoryChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter form used to select sheets of an event as targets of a messaging campaign (emailing).
 */
class TargetFilterType extends AbstractType
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var BooleanFiltersBuilder */
    private $booleanFilterBuilder;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param BooleanFiltersBuilder       $booleanFilterBuilder
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository, BooleanFiltersBuilder $booleanFilterBuilder)
    {
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
            ->add('text', Sheet\SheetTextSearchType::class, [
                'label'       => 'form.sheet_filter.children.text_search.label',
            ])
            ->add('validationState', Sheet\ValidationStateChoiceType::class, [
                'label'       => 'form.sheet_filter.children.validationState.label',
                'required'    => false,
                'multiple'    => true,
                'expanded'    => true,
            ])
            ->add('state', Sheet\StateChoiceType::class, [
                'label'       => 'form.sheet_filter.children.state.label',
                'multiple'    => true,
                'expanded'    => true,
            ])
            ->add('inCatalog', YesNoType::class, [
                'label'       => 'form.sheet_filter.children.inCatalog.label',
                'required'    => false,
            ])
            ->add('completed', Sheet\CompletedChoiceType::class, [
                'label'       => 'form.sheet_filter.children.completed.label',
                'required'    => false,
            ])
            ->add('type', TypeChoiceType::class, [
                'label'       => 'form.sheet_filter.children.type.label',
                'event'       => $event,
                'locale'      => $options['locale'],
                'user'        => $options['user'],
                'required'    => false,
                'expanded'    => true,
                'multiple'    => true,
            ])
            ->add('hasHappeningParticipation', YesNoType::class, [
                'label'       => 'form.sheet_filter.children.hasHappeningParticipation.label',
                'required'    => false,
            ])
            ->add('hasRemainingToPay', CheckboxType::class, [
                'label'       => 'form.sheet_filter.children.hasRemainingToPay.label',
                'required'    => false,
            ])
            ->add('hasNoMeetingRequest', CheckboxType::class, [
                'label'       => 'form.sheet_filter.children.hasNoMeetingRequest.label',
                'required'    => false,
            ])
            ->add('hasPendingMeetingPropositions', CheckboxType::class, [
                'label'       => 'form.sheet_filter.children.hasPendingMeetingPropositions.label',
                'required'    => false,
            ])
        ;

        $categories = $this->categoryRepository->getCategoriesByEvent($event, $options['locale']);

        if (!empty($categories)) {
            $builder->add('category', CategoryChoiceType::class, [
                'label'       => 'form.sheet_filter.children.category.label',
                'event'       => $event,
                'locale'      => $options['locale'],
                'required'    => false,
                'expanded'    => true,
                'multiple'    => true,
            ]);
        }

        $builder
            ->add('follower', Sheet\FollowerChoiceType::class, [
                'label'       => 'form.sheet_filter.children.follower.label',
                'event'       => $event,
                'unassigned'  => false,
                'required'    => false,
                'multiple'    => true,
                'expanded'    => true,
            ])
            ->add('registeredAt', Sheet\CreationIntervalFilterType::class, [
                'label'                     => 'form.sheet_filter.children.creation_interval.label',
                'required'                  => false,
                'choice_translation_domain' => 'messages',
            ])
            ->add(Constant::NO_ORDER, CheckboxType::class, [
                'label'              => 'admin.sheet.filter.no_order',
                'required'           => false,
                'translation_domain' => 'messages',
            ])
            ->add(Constant::HAS_CART, CheckboxType::class, [
                'label'              => 'admin.sheet.filter.has_cart',
                'required'           => false,
                'translation_domain' => 'messages'
            ])
        ;

        $booleanFilters = $this->booleanFilterBuilder->getFilters($event);

        if (!empty($booleanFilters)) {
            $builder->add('boolean_filters', ChoiceType::class, [
                'choices'            => array_flip($booleanFilters),
                'label'              => 'admin.sheet.filter.template_filters',
                'required'           => false,
                'multiple'           => true,
                'expanded'           => true,
                'translation_domain' => 'messages',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'user']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'targetting';
    }
}
