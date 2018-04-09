<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\Batch;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Group\GroupChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planning\OrderByChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BatchType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ids', ChoiceType::class, [
                'choices'            => $options['ids'],
                'choice_name'        => function ($id) {
                    return $id;
                },
                'expanded'           => true,
                'multiple'           => true,
                'label'              => false,
                'translation_domain' => false,
            ])
            ->add('follower', FollowerChoiceType::class, [
                'event'       => $options['event'],
                'placeholder' => '',
                'required'    => false,
                'unassigned'  => true,
            ])
            ->add('validateComment', TextareaType::class, [
                'required' => false,
            ])
            ->add('validate', SubmitType::class)
            ->add('assign', SubmitType::class)
            ->add('accept', SubmitType::class)
            ->add('refuse', SubmitType::class)
            ->add('pending', SubmitType::class)
            ->add('selectionType', HiddenType::class, [
                'data' => Batch::SELECTION_TYPE_PAGE,
            ])
        ;

        if ($this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            $builder
                ->add('printPdf', SubmitType::class)
                ->add('printPlanning', SubmitType::class)
                ->add('printPlanningOrderBy', OrderByChoiceType::class, [
                    'placeholder' => false,
                    'required' => false,
                ])
            ;
        }

        if ($this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ADMIN')) {
            $builder
                ->add('enable', SubmitType::class)
                ->add('disable', SubmitType::class)
                ->add('addCatalog', SubmitType::class)
                ->add('removeCatalog', SubmitType::class)
                ->add('generateInvoice', SubmitType::class)
                ->add('group', GroupChoiceType::class, [
                    'event'    => $options['event'],
                    'required' => false,
                ])
                ->add('assignToGroup', SubmitType::class)
            ;
        }

        $builder
            ->add('validationStateDraft', SubmitType::class)
            ->add('validationStateValidate', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['ids', 'event']);
        $resolver->setAllowedTypes('ids', ['array']);
        $resolver->setDefaults(['data_class' => Batch::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_batch';
    }
}
