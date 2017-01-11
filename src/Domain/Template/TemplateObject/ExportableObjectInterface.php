<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

/**
 * Interface to be implemented by all template objects that can be exported.
 */
interface ExportableObjectInterface
{
    /**
     * @return string
     */
    public function getExportableContent();

    /**
     * @param string $locale
     * @param string $fallback
     *
     * @return string
     */
    public function getExportableFieldname($locale, $fallback);
}
