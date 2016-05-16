<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Billing;

use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Update $update
     *
     * @throws RequiredDataEmptyException
     *
     * @deprecated to be rewritten
     */
    public function handle(Update $update)
    {
        $update->sheet->setBillingData($update->billingData);

        $this->sheetRepository->set($update->sheet);
    }
}
