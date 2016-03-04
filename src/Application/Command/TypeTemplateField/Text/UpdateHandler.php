<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Text;

use Proximum\Vimeet\Application\Command\TypeTemplateField\AbstractHandler;

class UpdateHandler extends AbstractHandler
{
    /**
     * @param Update $update
     *
     * @throws \Exception
     */
    public function handle(Update $update)
    {
        $options = $this->getOptions($update);

        $options['translatable']        = $update->translatable;
        $options['translationRequired'] = $update->translationRequired;

        $update->type->updateTemplateRow(
            $update->templateName,
            $update->field->getGroup()->getName(),
            $update->field->getName(),
            $options
        );

        $this->typeRepository->set($update->type);
    }
}
