<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Meeting;

use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestTo;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestUpdateToType extends AbstractMeetingRequestType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
           'data_class' => UpdateRequestTo::class,
           'submit'     => true,
        ]);
    }
}
