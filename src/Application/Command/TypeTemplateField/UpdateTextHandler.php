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

class UpdateTextHandler
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
     * @param UpdateLibText $update
     *
     * @throws \Exception
     */
    public function handle(UpdateLibText $update)
    {
        $options = $update->field->getOptions();

        $options['label'] = $update->label;
        $options['required'] = $update->required;
        $options['private'] = $update->private;
        $options['translatable'] = $update->translatable;
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
