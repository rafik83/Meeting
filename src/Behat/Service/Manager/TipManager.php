<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\TipFactory;

class TipManager
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var EventManager */
    private $eventManager;

    /** @var TypeManager */
    private $typeManager;

    /**
     * @param TipRepositoryInterface $tipRepository
     * @param EventManager           $eventManager
     * @param TypeManager            $typeManager
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        EventManager $eventManager,
        TypeManager $typeManager
    ) {
        $this->tipRepository = $tipRepository;
        $this->eventManager  = $eventManager;
        $this->typeManager   = $typeManager;
    }

    /**
     * @param string $tipTitle
     *
     * @return Tip
     */
    public function create($tipTitle = null)
    {
        $tip   = TipFactory::createTip($tipTitle);
        $event = $this->eventManager->create('Event_2');
        $type  = $this->typeManager->create($event);

        $tip->setType($type);

        $this->tipRepository->add($tip);

        return $tip;
    }
}
