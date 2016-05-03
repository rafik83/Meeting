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

class MediaCollection extends Object
{
    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        return parent::setData(array_merge(['medias' => []], $data));
    }

    /**
     * @return array
     */
    public function getMedias()
    {
        return array_values($this->data['medias']);
    }

    /**
     * @param array $media
     *
     * @return MediaCollection
     */
    public function addMedia(array $media)
    {
        $this->data['medias'][] = $media;

        return $this;
    }

    /**
     * @param array $media
     */
    public function removeMedia(array $media)
    {
        foreach ($this->data['medias'] as $key => $value) {
            if ($media === $value) {
                unset($this->data['medias'][$key]);
            }
        }
    }
}
