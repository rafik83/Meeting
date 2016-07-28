<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

class Url extends EditableObject implements ContentObjectInterface
{
    /**
     * @param string $url
     *
     * @return Url
     */
    public function setUrl($url)
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return isset($this->data['url']) ? $this->data['url'] : null;
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
    public function getContentValue()
    {
        return $this->getUrl() ? $this->getUrl() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setUrl($value);
    }
}
