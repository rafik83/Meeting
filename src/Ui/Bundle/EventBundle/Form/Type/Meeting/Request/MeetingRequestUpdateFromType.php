<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestFrom;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestUpdateFromType extends AbstractMeetingRequestType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
           'data_class' => UpdateRequestFrom::class,
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
