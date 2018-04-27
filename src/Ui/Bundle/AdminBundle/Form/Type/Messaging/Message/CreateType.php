<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Message;

use Proximum\Vimeet\Application\Command\Messaging\Message\Create as CreateMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateType extends AbstractMessageType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', CreateMessage::class);
    }
}
