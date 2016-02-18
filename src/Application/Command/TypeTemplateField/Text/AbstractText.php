<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Text;

use Proximum\Vimeet\Application\Command\TypeTemplateField\AbstractCommand;
use Proximum\Vimeet\Application\Components\Sheet\Template\Type\LibTextType;
use Proximum\Vimeet\Domain\Model\Type;

abstract class AbstractText extends AbstractCommand
{
    /**
     * @var bool
     */
    public $translatable;

    /**
     * @var bool
     */
    public $translationRequired;

    /**
     * @param Type        $type
     * @param string      $templateName
     * @param LibTextType $field
     */
    public function __construct(Type $type, $templateName, LibTextType $field)
    {
        parent::__construct($type, $templateName, $field);

        $this->translatable = $field->isTranslatable();
        $this->translationRequired = $field->isTranslationRequired();
    }
}
