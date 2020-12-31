<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class ButtonLink extends EditableObject implements ContentObjectInterface
{
    /**
     * @return string
     */
    public function getUrl()
    {
        return isset($this->data['url']) ? $this->data['url'] : null;
    }

    /**
     * @param string $url
     *
     * @return ButtonLink
     */
    public function setUrl($url)
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getUrl() ? $this->getUrl() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValueLocalize($locale = null)
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLabel()
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setUrl($value);
    }

    /**
     * {@inheritdoc}
     */
    public function isExportable()
    {
        return false;
    }
}
