<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class Gender extends EditableObject implements ContentObjectInterface, ExportableObjectInterface
{
    const MAN   = 'man';
    const WOMAN = 'woman';

    /**
     * @param string $gender
     *
     * @return Gender
     */
    public function setGender($gender)
    {
        $this->data['gender'] = $gender;

        return $this;
    }

    /**
     * @return array
     */
    public static function getGenders()
    {
        return [
            self::MAN   => self::MAN,
            self::WOMAN => self::WOMAN,
        ];
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return isset($this->data['gender']) ? $this->data['gender'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getGender() ? $this->getGender() : '';
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
        $this->setGender($value);
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
