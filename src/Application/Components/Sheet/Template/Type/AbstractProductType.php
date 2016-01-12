<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownOptionException;

abstract class AbstractProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['unitPrice']);
        $resolver->setDefined(['includedIn', 'quantity']);
    }

    /**
     * @return float
     * @throws UnknownOptionException
     */
    public function getUnitPrice()
    {
        return (float) $this->getOption('unitPrice');
    }
}
