<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class BooleanObject extends EditableObject implements ContentObjectInterface
{
    const YES = 'yes';
    const NO  = 'no';

    /**
     * @param bool $boolean
     *
     * @return BooleanObject
     */
    public function setBoolean($boolean)
    {
        $this->data['boolean'] = $boolean;

        return $this;
    }

    /**
     * @return array
     */
    public static function getBooleanValues()
    {
        return [
            self::YES => true,
            self::NO  => false,
        ];
    }

    /**
     * @return bool
     */
    public function isFilter()
    {
        $filter = $this->getOption('filter');

        return null !== $filter
            && isset($filter['active'])
            && true === $filter['active'];
    }

    /**
     * @return string
     */
    public function getFilterLabel()
    {
        $filter = $this->getOption('filter');

        return null !== $filter
            && isset($filter['label'])
            && null !== $filter['label'] ? $filter['label'] : '';
    }

    /**
     * @return string
     */
    public function getBoolean()
    {
        return isset($this->data['boolean']) ? $this->data['boolean'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return null !== $this->getBoolean() ? ($this->getBoolean() ? self::YES : self::NO) : null;
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
        $this->setBoolean($value);
    }
}
