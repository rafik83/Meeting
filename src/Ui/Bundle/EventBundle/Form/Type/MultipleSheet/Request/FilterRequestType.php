<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MultipleSheet\Request;

use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('state', ChoiceType::class, [
                'required' => false,
                'choices' => Request::getAllStates(),
                'choice_label' => function ($value) {
                    return 'form.multiple_sheet_request_filter_request_type.children.state.filter.' . $value;
                },
                'placeholder' => 'form.multiple_sheet_request_filter_request_type.children.state.filter.all',
            ])
            ->add('type', ChoiceType::class, [
                'required'     => false,
                'choices'      => [Request::TYPE_REQUEST, Request::TYPE_PROPOSITION],
                'choice_label' => function ($value) {
                    return 'form.multiple_sheet_request_filter_request_type.children.type.filter.' . $value;
                },
                'placeholder'  => 'form.multiple_sheet_request_filter_request_type.children.type.filter.all',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', FilterRequestView::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'multiple_sheet_request_filter_request_type';
    }
}
