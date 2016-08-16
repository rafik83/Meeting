<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateDataHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var BuyableObjectResolver
     */
    private $buyableObjectResolver;

    /**
     * UpdateDataHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param BuyableObjectResolver    $buyableObjectResolver
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BuyableObjectResolver $buyableObjectResolver
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->buyableObjectResolver = $buyableObjectResolver;
    }

    /**
     * @param UpdateData $command
     */
    public function handle(UpdateData $command)
    {
        $this->buyableObjectResolver->updateCart($command->sheet, $command->templateData);
        $this->sheetRepository->set($command->sheet->setData($command->templateData->getData()));
    }
}
