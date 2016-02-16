<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Position;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class PositionHandler
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
     * @param Position $position
     */
    public function handle(Position $position)
    {
        $rows  = [];
        $types = $position->group->getTypes();
        asort($position->fieldsOrder);

        $loop = 1;
        foreach ($position->fieldsOrder as $fieldName => $value) {
            $types[$fieldName]->setPosition($loop);
            $rows[$fieldName] = $types[$fieldName]->getOptions();
            $loop++;
        }

        $position->type->setTemplateRows($position->templateName, $position->group->getName(), $rows);
        $this->typeRepository->set($position->type);
    }
}
