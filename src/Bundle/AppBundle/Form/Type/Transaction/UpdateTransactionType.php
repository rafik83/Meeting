<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Transaction;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Proximum\Vimeet\Application\Command\Transaction\Update;

class UpdateTransactionType extends AbstractTransactionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
            'submit'     => true,
        ]);
    }
}
