<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class TagsCollection extends ItemCollection
{
    /**
     * {@inheritdoc}
     */
    public function __construct($key, $type, array $config, $locale, $fallback)
    {
        parent::__construct($key, $type, $config, $locale, $fallback);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->getOption('tags');
    }

    /**
     * @return bool
     */
    public function isCollectionEnabled()
    {
        return (bool) $this->getOption('collection');
    }

    /**
     * {@inheritdoc}
     */
    public function isExportable()
    {
        return false;
    }
}
