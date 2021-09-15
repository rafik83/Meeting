<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class BooleanObject extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    const YES = true;
    const NO  = false;

    /**
     * @param bool $boolean
     *
     * @return BooleanObject
     */
    public function setBoolean($boolean)
    {
        $this->data['boolean'] = $boolean;

        return $this;
    }

    /**
     * @return array
     */
    public static function getBooleanValues()
    {
        return [
            'boolean.yes' => true,
            'boolean.no'  => false,
        ];
    }

    /**
     * @return bool
     */
    public function isFilter()
    {
        $filter = $this->getOption('filter');

        return null !== $filter
            && isset($filter['active'])
            && true === $filter['active'];
    }

    /**
     * @return string
     */
    public function getFilterLabel()
    {
        $filter = $this->getOption('filter');

        return null !== $filter
            && isset($filter['label'])
            && null !== $filter['label'] ? $filter['label'] : '';
    }

    /**
     * @return string
     */
    public function getBoolean()
    {
        return isset($this->data['boolean']) ? $this->data['boolean'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return null !== $this->getBoolean() ? ($this->getBoolean() ? self::YES : self::NO) : null;
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
        $this->setBoolean($value);
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
     *
     * @return bool
     */
    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        return (bool) $this->getBoolean();
    }
}
