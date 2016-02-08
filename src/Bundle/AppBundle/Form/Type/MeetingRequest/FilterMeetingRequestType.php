<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FilterMeetingRequestType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('state', ChoiceType::class, [
                'label'             => false,
                'choices_as_values' => true,
                'choices'           => [
                    'form.admin.meeting_request.list.filter.state.children.approved' => Request::STATE_APPROVED,
                    'form.admin.meeting_request.list.filter.state.children.cancel'   => Request::STATE_CANCEL,
                    'form.admin.meeting_request.list.filter.state.children.refused'  => Request::STATE_REFUSED,
                    'form.admin.meeting_request.list.filter.state.children.sent'     => Request::STATE_SENT,
                ],
                'placeholder' => '',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.admin.meeting_request.list.filter.children.submit.label'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required'        => false,
            'method'          => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
