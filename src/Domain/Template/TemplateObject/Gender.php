<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class Gender extends EditableObject implements ContentObjectInterface
{
    const MAN   = 'man';
    const WOMAN = 'woman';

    /**
     * @param string $gender
     *
     * @return Gender
     */
    public function setGender($gender)
    {
        $this->data['gender'] = $gender;

        return $this;
    }

    /**
     * @return array
     */
    public static function getGenders()
    {
        return [
            self::MAN   => self::MAN,
            self::WOMAN => self::WOMAN,
        ];
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return isset($this->data['gender']) ? $this->data['gender'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getGender() ? $this->getGender() : '';
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
