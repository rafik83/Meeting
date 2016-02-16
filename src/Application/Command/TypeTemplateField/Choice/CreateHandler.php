<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Choice;

use Proximum\Vimeet\Application\Command\TypeTemplateField\AbstractHandler;

class CreateHandler extends AbstractHandler
{
    /**
     * @param Create $create
     *
     * @throws \Exception
     */
    public function handle(Create $create)
    {
        $options = $this->getOptions($create);

        $options['choices'] = $create->choices;

        $create->type->addTemplateRow(
            $create->templateName,
            $create->field->getGroup()->getName(),
            $create->field->getName(),
            $options
        );

        $this->typeRepository->set($create->type);
    }
}
