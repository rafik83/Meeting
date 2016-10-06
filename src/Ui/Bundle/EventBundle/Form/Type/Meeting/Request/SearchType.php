<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Meeting\TypeViewTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderBy', ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.alphabetical' => Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.createdAt'    => Constant::ORDER_BY_CREATED_AT,
                ],
            ])
            ->add('state', ChoiceType::class, [
                'label'        => 'form.search.meeting.state.label',
                'expanded'     => true,
                'multiple'     => false,
                'choices'      => MeetingRequest::getAllStates(),
                'choice_value' => function ($state) {
                    return $state;
                },
                'choice_label' => function ($state) {
                    return 'form.search.meeting.state.' . $state;
                },
            ])
            ->add('type', ChoiceType::class, [
                'label'        => 'form.search.type.label',
                'expanded'     => true,
                'multiple'     => true,
                'choices'      => $options['typeViews'],
                'choice_value' => function (TypeView $typeView) {
                    return $typeView->id;
                },
                'choice_label' => function (TypeView $typeView) {
                    return $typeView->title;
                },
            ]);

        $builder->get('type')->addModelTransformer(new TypeViewTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['typeViews']);
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
        return '';
    }
}
