<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class Telephone extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    /**
     * @param string $telephone
     *
     * @return Telephone
     */
    public function setTelephone($telephone)
    {
        $this->data['telephone'] = $telephone;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return isset($this->data['telephone']) ? $this->data['telephone'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getTelephone() ? $this->getTelephone() : '';
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
        $this->setTelephone($value);
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
