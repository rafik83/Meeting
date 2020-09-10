<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class MediaCollection extends TemplateObject
{
    /**
     * @var Media[]
     */
    private $medias = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($key, $type, array $config, $locale, $fallback)
    {
        parent::__construct($key, $type, $config, $locale, $fallback);

        $this->padMedias();
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $data = array_merge(['medias' => []], $data);

        $this->buildMedias($data);
        $this->padMedias();

        return parent::setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->data['medias'] = array_values(array_map(function (Media $media) {
            return $media->getData();
        }, $this->getNotEmptyMedias()));

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

    /**
     * Get default medias count
     *
     * @return int
     */
    public function getDefault()
    {
        return $this->getOption('default');
    }

    /**
     * Pad medias
     */
    private function padMedias()
    {
        $default = $this->getDefault();

        if (!empty($default)) {
            $pad = $default - \count($this->medias);
            while ($pad-- > 0) {
                $this->medias[] = new Media($this, null, null, null);
            }
        }
    }

    /**
     * Build medias
     *
     * @param array $data
     */
    private function buildMedias(array $data)
    {
        $this->medias = array_map(
            function (array $media) {
                return new Media($this, $media['title'], $media['url'], $media['type']);
            }, array_values($data['medias'])
        );
    }

    /**
     * Get not empty medias
     *
     * @return array
     */
    public function getNotEmptyMedias()
    {
        return array_filter(array_values($this->medias), function (Media $media) {
            return !$media->isEmpty();
        });
    }

    public function getTitlePlaceholder(): ?string
    {
        return $this->getOption('titlePlaceholder', $this->locale);
    }

    public function getLinkPlaceholder(): ?string
    {
        return $this->getOption('linkPlaceholder', $this->locale);
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->getOption('max');
    }

    /**
     * {@inheritdoc}
     */
    public function isExportable()
    {
        return false;
    }

    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        $exportableContents = array_map(static function (Media $media) {
            return $media->url ?? '';
        }, $this->getMedias());

        return implode(';', $exportableContents);
    }
}
