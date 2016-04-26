<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\TemplateNotFoundException;

class Templates
{
    /**
     * @var Template[]
     */
    private $templates;

    /**
     * Templates constructor.
     *
     * @param Template[] $templates
     */
    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * Get templates
     *
     * @return Template[]
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param $key
     *
     * @return Template
     * @throws TemplateNotFoundException
     */
    public function getTemplate($key)
    {
        if (isset($this->templates[$key])) {
            return $this->templates[$key];
        }

        throw new TemplateNotFoundException($key);
    }

    /**
     * @param string $tag
     * @param string $locale
     * @param array  $data
     *
     * @return array;
     */
    public function getTaggedValues($tag, $locale, array $data)
    {
        $values = [];

        foreach ($data as $key => $value) {
            $values = array_merge($values, $this->getTemplate($key)->getTaggedValues($tag, $locale, $value));
        }

        return $values;
    }

    /**
     * @param string $tag
     * @param string $locale
     * @param array  $data
     *
     * @return mixed
     */
    public function getTaggedValue($tag, $locale, array $data)
    {
        $values = $this->getTaggedValues($tag, $locale, $data);

        return reset($values);
    }
}
