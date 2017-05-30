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

    /**
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param string $tipTitle
     *
     * @return Tip
     */
    public function create($tipTitle = null)
    {
        $tip = TipFactory::createTip($tipTitle);
        $this->tipRepository->add($tip);

        return $tip;
    }
}
