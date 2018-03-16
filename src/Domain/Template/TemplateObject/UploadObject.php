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

class UploadObject extends TemplateObject
{
    public const FORMAT_IMAGE = 'image';
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_PPT = 'ppt';
    public const FORMAT_CSV = 'csv';

    public const ALLOWED_FORMATS = [
        self::FORMAT_IMAGE,
        self::FORMAT_PDF,
        self::FORMAT_PPT,
        self::FORMAT_CSV,
    ];

    /**
     * @return bool
     */
    public function isCrypted(): bool
    {
        return null !== $this->getOption('crypted') && true === $this->getOption('crypted');
    }

    /**
     * @return bool
     */
    public function isFilter(): bool
    {
        $filter = $this->getOption('filter');

        return null !== $filter
            && isset($filter['active'])
            && true === $filter['active'];
    }

    /**
     * @return string
     */
    public function getFilterLabel(): string
    {
        $filter = $this->getOption('filter');

        return null !== $filter
        && isset($filter['label'])
        && null !== $filter['label'] ? $filter['label'] : '';
    }
}
