<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField;

use Proximum\Vimeet\Application\Components\Sheet\Template\TypeInterface;
use Proximum\Vimeet\Domain\Model\Type;

class Update
{
    /**
     * @var Type
     */
    public $type;

    /**
     * @var TypeInterface
     */
    public $field;

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
     * @param Type               $type
     * @param string             $templateName
     * @param TypeInterface      $field
     *
     * @throws \Exception
     */
    public function __construct(Type $type, $templateName, TypeInterface $field)
    {
        $this->type         = $type;
        $this->templateName = $templateName;
        $this->field        = $field;

        $templates = $type->getTemplates();

        if (!isset($templates[$this->templateName . 'Template'])) {
            throw new \Exception("Template $templateName invalid");
        }

        $this->required = $field->isRequired();
        $this->private  = $field->isPrivate();

        foreach ($type->getEvent()->getLocales() as $locale) {
            $this->label[$locale] = $field->getLabel($locale);
        }
    }
}
