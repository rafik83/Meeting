<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Command\Nomenclature\Exception\MissingKeysException;
use Proximum\Vimeet\Application\Nomenclature\Import\ImporterInterface;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
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
     *
     * @throws MissingKeysException
     */
    public function handle(Import $command)
    {
        $originalKeys = $this->getKeys($command->nomenclature);

        $this->importer->import($command->nomenclature, $command->filename);

        if (false) {
            $updateKeys  = $this->getKeys($command->nomenclature);
            $missingKeys = array_diff_key($originalKeys, $updateKeys);

            if (count($missingKeys) > 0) {
                throw new MissingKeysException($missingKeys);
            }
        }

        $this->nomenclatureRepository->set($command->nomenclature);
    }

    /**
     * @param Nomenclature $nomenclature
     *
     * @return array
     */
    private function getKeys(Nomenclature $nomenclature)
    {
        return array_map(function (NomenclatureItem $item) {
            return $item->getKey();
        }, $nomenclature->getLastLevel());
    }
}

