<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Symfony\Component\Intl\Countries;

class Country extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    /**
     * @param string $country
     *
     * @return Country
     */
    public function setCountry($country)
    {
        $this->data['country'] = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return isset($this->data['country']) ? $this->data['country'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getCountry() ? $this->getCountry() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLabel()
    {
        return $this->getCountry() ? Countries::getName($this->getCountry()) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setCountry($value);
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
    public function getExportableFieldname($locale, $fallback)
    {
        return $this->getLabel($locale, $fallback);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        return $this->getContentLabel();
    }
}
