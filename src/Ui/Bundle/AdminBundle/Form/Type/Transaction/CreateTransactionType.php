<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Transaction;

use Proximum\Vimeet\Application\Command\Transaction\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateTransactionType extends AbstractTransactionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Create::class,
            'submit'     => true,
        ]);
    }
}
