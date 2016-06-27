<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\Import\ImporterInterface;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class ImportHandler
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * @var ImporterInterface
     */
    private $importer;

    /**
     * ImportHandler constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param ImporterInterface               $importer
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository, ImporterInterface $importer)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->importer               = $importer;
    }

    /**
     * @var Import $command
     */
    public function handle(Import $command)
    {
        $this->importer->import($command->nomenclature, $command->filename);
        $this->nomenclatureRepository->set($command->nomenclature);
    }
}

