<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /**
     * @param MassRepositoryInterface $massRepository
     */
    public function __construct(MassRepositoryInterface $massRepository)
    {
        $this->massRepository = $massRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->mass->update(
            $update->category,
            $update->name,
            $update->begin,
            $update->end,
            $update->blocking
        );

        foreach ($update->translations as $locale => $translation) {
            $update->mass->updateTranslation($locale, $translation['title'], $translation['description']);
        }

        $this->massRepository->update($update->mass);
    }
}
