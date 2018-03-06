<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class Image extends EditableObject implements ContentObjectInterface
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getData() ? $this->getData() : '';
    }

    /**
     * @param string $image
     *
     * @return Image
     */
    public function setImage($image)
    {
        $this->data['image'] = $image;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImage()
    {
        return isset($this->data['image']) ? $this->data['image'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getImage() ? $this->getImage() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValueLocalize($locale = null)
    {
        return $this->getContentValue();
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
        $this->setImage($value);
    }

    /**
     * @return array
     */
    public static function supportedMimeType()
    {
        return [
            "image/gif",
            "image/jpeg",
            "image/pjpeg",
            "image/png",
            "image/x-png",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isExportable()
    {
        return false;
    }
}
