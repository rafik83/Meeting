<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Choice;

use Proximum\Vimeet\Application\Command\TypeTemplateField\AbstractCommand;
use Proximum\Vimeet\Application\Components\Sheet\Template\Type\LibChoiceType;
use Proximum\Vimeet\Domain\Model\Type;

abstract class AbstractChoice extends AbstractCommand
{
    /**
     * @var array
     */
    public $choices = [];

    /**
     * UpdateLibChoice constructor.
     *
     * @param Type          $type
     * @param string        $templateName
     * @param LibChoiceType $field
     */
    public function __construct(Type $type, $templateName, LibChoiceType $field)
    {
        parent::__construct($type, $templateName, $field);

        $this->choices = $field->getChoices();

        // ensure that all choices has all event locales label translations
        foreach ($this->choices as $key => $choice) {
            foreach ($type->getEvent()->getLocales() as $locale) {
                $this->choices[$key]['label'][$locale] = isset($choice['label'][$locale])
                    ? $choice['label'][$locale]
                    : '';
            }
        }
    }
}
