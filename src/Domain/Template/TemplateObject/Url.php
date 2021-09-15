<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class Url extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    /**
     * @param string $url
     *
     * @return Url
     */
    public function setUrl($url)
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return isset($this->data['url']) ? $this->data['url'] : null;
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
    public function getContentValue()
    {
        return $this->getUrl() ? $this->getUrl() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getContentValueLocalize($locale = null)
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
    public function getExportableFieldname($locale, $fallback)
    {
        return $this->getLabel($locale, $fallback);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        $result = $this->getContentValue();

        if (!empty($result)) {
            return $result;
        }

        if (isset($taggedData[$this->getTag()])) {
            if (is_array($taggedData[$this->getTag()])) {
                return implode(', ', $taggedData[$this->getTag()]);
            }

            return $taggedData[$this->getTag()];
        }

        return '';
    }
}
