<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestCreateType extends AbstractMeetingRequestType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('placeholder_description', 'form.catalog_create_meeting_request.children.description.placeholder');
        $resolver->setDefaults([
            'data_class' => CreateRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_create_meeting_request';
    }
}
