<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Command\Nomenclature;


use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class AssignHandler
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * CreateHandler constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * @var Assign $command
     *
     * @return AssignResult
     */
    public function handle(Assign $command)
    {
        $nomenclature = new Nomenclature(
            $command->nomenclature->getTitle(),
            $command->nomenclature->getDepth(),
            $command->nomenclature->getValue(),
            $command->nomenclature->isSorted(),
            $command->event
        );

        $this->nomenclatureRepository->add($nomenclature);

        return new AssignResult($nomenclature);
    }
}
