<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandler
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
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $type = $update->type;

        foreach ($type->getTranslations() as $translation) {
            if (isset($update->translations[$translation->getLocale()])) {
                $translation->update($update->translations[$translation->getLocale()]['title']);
            }
        }

        $this->typeRepository->set($type);
    }
}
