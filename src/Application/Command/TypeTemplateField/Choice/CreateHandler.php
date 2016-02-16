<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Choice;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CreateHandler
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
     * @param Create $create
     *
     * @throws \Exception
     */
    public function handle(Create $create)
    {
        $options = $create->field->getOptions();

        $options['label'] = $create->label;
        $options['required'] = $create->required;
        $options['private'] = $create->private;
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
