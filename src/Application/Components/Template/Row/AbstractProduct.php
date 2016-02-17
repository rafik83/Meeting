<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Row;

use Proximum\Vimeet\Application\Components\Template\Row;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProduct extends Row
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'unitPrice'      => 0,
            'updatableUntil' => null,
            'quantity'       => 1,
            'includedIn'     => [],
        ]);
        $resolver->setAllowedTypes('unitPrice', ['int', 'float']);
        $resolver->setAllowedTypes('quantity', ['int', 'array']);
        $resolver->setAllowedTypes('includedIn', ['array']);
        $resolver->setAllowedTypes('updatableUntil', ['null', 'string']);
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatableUntil()
    {
        return $this->options['updatableUntil'] ? new \DateTime($this->options['updatableUntil']) : null;
    }

    /**
     * @return bool
     */
    public function isUpdatable()
    {
        return $this->getUpdatableUntil() > new \DateTime();
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->options['quantity'];
    }

    /**
     * @return array
     */
    public function getIncludedIn()
    {
        return $this->options['includedIn'];
    }
}
