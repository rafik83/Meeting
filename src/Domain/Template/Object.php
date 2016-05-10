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
     * @return Object
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return Object
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
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag($tag)
    {
        return isset($this->config['tags']) && is_array($this->config['tags']) && in_array($tag, $this->config['tags']);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return isset($this->config['tags']) && is_array($this->config['tags']) ? $this->config['tags'] : [];
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

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getData();
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return false;
    }
}
