<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Billing;

use Proximum\Vimeet\Application\Components\Template\Validator;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * UpdateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param Validator                $validator
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, Validator $validator)
    {
        $this->sheetRepository = $sheetRepository;
        $this->validator       = $validator;
    }

    /**
     * @param Update $update
     *
     * @throws RequiredDataEmptyException
     */
    public function handle(Update $update)
    {
        $this->validator->validateBillingData($update->sheet, $update->billingData);
        $update->sheet->setBillingData($update->billingData);

        $this->sheetRepository->set($update->sheet);
    }
}
