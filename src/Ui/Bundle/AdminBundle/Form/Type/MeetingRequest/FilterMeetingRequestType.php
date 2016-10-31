<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'label'                     => 'form.meeting_request_filter.children.state.label',
                'choices'                   => [
                    'admin.meeting_request.state.approved'  => Request::STATE_APPROVED,
                    'admin.meeting_request.state.refused'   => Request::STATE_REFUSED,
                    'admin.meeting_request.state.sent'      => Request::STATE_SENT,
                ],
                'placeholder'               => '',
                'choice_translation_domain' => 'messages',
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
