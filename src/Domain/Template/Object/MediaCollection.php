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
     * @var Media[]
     */
    private $medias = [];

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $data = array_merge(['medias' => []], $data);

        $this->medias = array_map(function (array $media) {
            return new Media($this, $media['title'], $media['url'], $media['type']);
        }, array_values($data['medias']));

        return parent::setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->data['medias'] = array_values(array_map(function (Media $media) {
            return $media->getData();
        }, $this->medias));

        return parent::getData();
    }

    /**
     * @return array
     */
    public function getMedias()
    {
        return $this->medias;
    }

    /**
     * @param Media $media
     *
     * @return MediaCollection
     */
    public function addMedia(Media $media)
    {
        $this->medias[] = $media;

        return $this;
    }

    /**
     * @param Media $media
     *
     * @return MediaCollection
     */
    public function removeMedia(Media $media)
    {
        foreach ($this->medias as $key => $value) {
            if ($media === $value) {
                unset($this->medias[$key]);
            }
        }

        return $this;
    }
}
