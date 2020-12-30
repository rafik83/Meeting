<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

/**
 * Interface to be implemented by all template objects that can be exported.
 */
interface ExportableObjectInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @param array       $taggedData is used to return possible value from the taggedData if the field is empty
     * @param string|null $locale
     *
     * @return string
     */
    public function getExportableContent(array $taggedData = [], ?string $locale = null);

    /**
     * @param string $locale
     * @param string $fallback
     *
     * @return string
     */
    public function getExportableFieldname($locale, $fallback);
}
