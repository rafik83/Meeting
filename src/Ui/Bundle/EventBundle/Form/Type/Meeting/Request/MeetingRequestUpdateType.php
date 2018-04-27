<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\UpdateMeetingRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestUpdateType extends AbstractMeetingRequestType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('placeholder_description', 'form.catalog_edit_meeting_request.children.description.placeholder');
        $resolver->setDefaults([
           'data_class' => UpdateMeetingRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_edit_meeting_request';
    }
}
