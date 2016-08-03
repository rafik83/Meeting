<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class RemoveHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function handle(Remove $remove)
    {
        $this->typeRepository->remove($remove->type);
    }
}
