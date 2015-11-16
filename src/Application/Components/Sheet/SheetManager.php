<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Apply\Applier;
use Proximum\Vimeet\Application\Components\Sheet\Apply\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SeeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SheetManager
{
    /**
     * @var SeeRepositoryInterface
     */
    private $seeRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * SheetManager constructor.
     *
     * @param SeeRepositoryInterface  $seeRepository
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(SeeRepositoryInterface $seeRepository, TypeRepositoryInterface $typeRepository) {
        $this->seeRepository  = $seeRepository;
        $this->typeRepository = $typeRepository;
    }

    /**
     * Get data seeable by $user
     *
     * @param Sheet $sheet
     * @param User  $user
     */
    public function apply(Sheet $sheet, User $user)
    {
        $applier = new Applier();
        $applier->apply($this->getSeeToApply($sheet, $user), $sheet, new SetNullStrategy());
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return See
     */
    private function getSeeToApply(Sheet $sheet, User $user)
    {
        $types = $this->typeRepository->getTypesByUser($user);
        $sees  = [];

        foreach ($types as $type) {
            $sees[] = $this->seeRepository->getBySeerTypeAndSeeableType($type, $sheet->getType());
        }

        return $sees[0];
    }
}
