<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField;

use Proximum\Vimeet\Domain\Model\Type;

class Update
{
    /**
     * @var Type
     */
    public $type;

    /**
     * @var array|null
     */
    public $field;

    /**
     * @var string
     */
    public $fieldType;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $templateName;

    /**
     * @var array
     */
    public $label = [];

    /**
     * @var bool
     */
    public $required;

    /**
     * @var bool
     */
    public $private;

    /**
     * @param Type   $type
     * @param string $templateName
     * @param string $key
     *
     * @throws \Exception
     */
    public function __construct(Type $type, $templateName, $key)
    {
        $this->type = $type;
        $this->templateName = $templateName;
        $this->key = $key;
        $this->field = null;

        $templates = $type->getTemplates();

        if (!isset($templates[$templateName])) {
            throw new \Exception("Template $templateName invalid");
        }

        $template = $templates[$templateName];
        $this->field = $this->getArrayByKey($key, $template);

        if (null === $this->field) {
            throw new \Exception("Field key not found in template $templateName");
        }

        $this->fieldType = $this->field['type'];
        $this->required = isset($this->field['required']) ? $this->field['required'] : false;
        $this->private = isset($this->field['private']) ? $this->field['private'] : false;

        foreach ($type->getEvent()->getLocales() as $locale) {
            $this->label[$locale] = isset($this->field['label'][$locale]) ? $this->field['label'][$locale] : '';
        }
    }

    /**
     * @return array
     */
    public function getFieldTemplate()
    {
        return [
            'type' => $this->fieldType,
            'label' => $this->label,
            'required' => $this->required,
            'private' => $this->private,
        ];
    }

    /**
     * @param string $key
     * @param array  $array
     *
     * @return null|array
     */
    private function getArrayByKey($key, $array)
    {
        foreach ($array as $index => $value) {
            if ($index === $key) {
                return $value;
            }

            if (is_array($value)) {
                $subValue = $this->getArrayByKey($key, $value);

                if (null !== $subValue) {
                    return $subValue;
                }
            }
        }

        return;
    }
}
