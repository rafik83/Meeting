<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class RemoveHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param TypeRepositoryInterface  $typeRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->typeRepository  = $typeRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Remove $remove
     *
     * @return null|bool
     */
    public function handle(Remove $remove)
    {
        if ($this->sheetRepository->isThereAtLeastOneByType($remove->type)) {
            return false;
        }

        $this->typeRepository->remove($remove->type);
    }
}
