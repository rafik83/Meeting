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

class MultiUploadCollectionObject extends TemplateObject
{
    /** @var MultiUploadObject[] */
    public $uploads = [];

    public function __construct($key, $type, array $config, $locale, $fallback)
    {
        parent::__construct($key, $type, $config, $locale, $fallback);

        $this->padUploads();
    }

    public function getTitlePlaceholder(): ?string
    {
        return $this->getOption('titlePlaceholder', $this->locale);
    }

    private function padUploads(): void
    {
        $default = $this->getDefault();

        if (!empty($default)) {
            $pad = $default - \count($this->uploads);
            while ($pad-- > 0) {
                $this->uploads[] = new MultiUploadObject(null, null);
            }
        }
    }

    public function getDefault()
    {
        return $this->getOption('default');
    }

    public function getMax(): ?int
    {
        return $this->getOption('max');
    }

    public function setData(array $data)
    {
        $this->buildUploads($data);
        $this->padUploads();

        return parent::setData($data);
    }

    private function buildUploads(array $data): void
    {
        $this->uploads = array_map(
            function (array $upload) {
                return new MultiUploadObject($upload['title'], $upload['path']);
            }, array_values($data)
        );
    }

    public function getFormats(): ?array
    {
        return $this->getOption('formats');
    }

    public function getUploads(): array
    {
        return $this->uploads;
    }
}
