<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Nomenclature;


use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class NomenclatureCloner
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * NomenclatureCloner constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * @param Nomenclature $nomenclature
     * @param Event        $event
     *
     * @return Nomenclature
     */
    public function dublicate(Nomenclature $nomenclature, Event $event)
    {
        $nomenclature = new Nomenclature(
            $nomenclature->getTitle(),
            $nomenclature->getDepth(),
            $nomenclature->getValue(),
            $nomenclature->isSorted(),
            $event
        );

        $this->nomenclatureRepository->add($nomenclature);

        return $nomenclature;
    }
}