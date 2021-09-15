<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

interface ContentObjectInterface
{
    /**
     * @return string
     */
    public function getContentValue();

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getContentValueLocalize($locale = null);

    /**
     * @return string
     */
    public function getContentLabel();

    /**
     * @param string|array $value
     */
    public function setContentValue($value);
}
