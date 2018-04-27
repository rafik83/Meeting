<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestApproveType extends AbstractMeetingRequestType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sheet', 'locale']);
        $resolver->setAllowedTypes('sheet', Sheet::class);
        $resolver->setDefault('placeholder_description', 'form.catalog_approve_meeting_request.children.description.placeholder');
        $resolver->setDefault('show_description', true);
        $resolver->setDefaults([
            'data_class' => ApproveRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_approve_meeting_request';
    }
}
