<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateChoiceHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param UpdateLibChoice $update
     *
     * @throws \Exception
     */
    public function handle(UpdateLibChoice $update)
    {
        $options = $update->field->getOptions();

        $options['label'] = $update->label;
        $options['required'] = $update->required;
        $options['private'] = $update->private;
        $options['choices'] = $update->choices;

        $update->type->updateTemplateRow(
            $update->templateName,
            $update->field->getGroup()->getName(),
            $update->field->getName(),
            $options
        );

        $this->typeRepository->set($update->type);
    }
}
