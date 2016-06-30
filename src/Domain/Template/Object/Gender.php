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

class Gender extends EditableObject implements ContentObjectInterface
{
    /**
     * @param bool $gender
     *
     * @return gender
     */
    public function setGender($gender)
    {
        $this->data['gender'] = $gender;

        return $this;
    }

    /**
     * @return bool
     */
    public function getGender()
    {
        return $this->data['gender'];
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getGender();
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
        $this->setGender($value);
    }
}
