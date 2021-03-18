<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class Text extends TemplateObject
{
    const TYPE_TITLE = 'title';
    const TYPE_TEXT  = 'text';

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getContent($locale)
    {
        return $this->getOption('content', $locale);
    }

    /**
     * @return bool
     */
    public function isTitle()
    {
        return self::TYPE_TITLE === $this->getOption('type');
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getOption('type');
    }

    /**
     * {@inheritdoc}
     */
    public function isExportable()
    {
        return false;
    }
}
