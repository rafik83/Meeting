<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting;

use Proximum\Vimeet\Application\Command\MeetingRequest\EditRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestEditType extends AbstractMeetingRequestType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
           'data_class' => EditRequest::class,
           'submit'     => true,
        ]);
    }
}
