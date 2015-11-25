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

class UpdateChoice extends Update
{
    /**
     * @var array
     */
    public $choices = [];

    /**
     * @param Type   $type
     * @param string $templateName
     * @param string $key
     *
     * @throws \Exception
     */
    public function __construct(Type $type, $templateName, $key)
    {
        parent::__construct($type, $templateName, $key);

        $this->choices = $this->field['choices'];

        // ensure that all choices has all event locales label translations
        foreach ($this->choices as $key => $choice) {
            foreach ($type->getEvent()->getLocales() as $locale) {
                $this->choices[$key]['label'][$locale] = isset($choice['label'][$locale])
                    ? $choice['label'][$locale]
                    : '';
            }
        }
    }

    /**
     * @return array
     */
    public function getFieldTemplate()
    {
        $fieldTemplate = parent::getFieldTemplate();

        $fieldTemplate['choices'] = $this->choices;

        return $fieldTemplate;
    }
}
