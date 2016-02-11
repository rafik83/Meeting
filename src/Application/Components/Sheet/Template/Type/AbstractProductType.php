<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Type;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownOptionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['unitPrice']);
        $resolver->setDefaults([
            'quantity'   => 1,
            'includedIn' => [],
        ]);
    }

    /**
     * @throws UnknownOptionException
     * @return float
     *
     */
    public function getUnitPrice()
    {
        return (float) $this->getOption('unitPrice');
    }

    /**
     * @throws UnknownOptionException
     * @return array
     *
     */
    public function getIncludedIn()
    {
        return $this->getOption('includedIn');
    }
}
