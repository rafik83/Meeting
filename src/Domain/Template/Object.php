<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

class Object extends AbstractChild
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $locale;

    /**
     * Set data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize()
    {
        return [
            'component' => 'object',
            'type'      => $this->type,
            'config'    => $this->config,
        ];
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $locale
     * @param string $fallback
     *
     * @return string
     */
    public function getLabel($locale, $fallback)
    {
        $label = $this->getOption('label');

        return $locale ? isset($label[$locale]) ? $label[$locale] : $this->getLabel($fallback, null) : null;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function missingRequiredData(array $data)
    {
        if (true === $this->getOption('required')) {
            return !empty($data[$this->getKey()]);
        }

        return true;
    }
}
