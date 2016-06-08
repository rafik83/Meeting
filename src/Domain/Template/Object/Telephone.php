<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;

class Telephone extends EditableObject implements ContentObjectInterface
{
    /**
     * @param string $telephone
     *
     * @return Telephone
     */
    public function setTelephone($telephone)
    {
        $this->data['telephone'] = $telephone;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return isset($this->data['telephone']) ? $this->data['telephone'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getTelephone() ? $this->getTelephone() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLabel()
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setTelephone($value);
    }
}
