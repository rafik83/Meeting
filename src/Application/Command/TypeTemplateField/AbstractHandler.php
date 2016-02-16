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

abstract class AbstractHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    public $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param mixed $command
     *
     * @return array
     */
    public function getOptions($command)
    {
        $options = $command->field->getOptions();

        $options['label']    = $command->label;
        $options['required'] = $command->required;
        $options['private']  = $command->private;
        $options['tags']     = $command->tags;

        return $options;
    }
}
