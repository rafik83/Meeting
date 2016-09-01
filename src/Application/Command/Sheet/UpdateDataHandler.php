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
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;

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
     * @var RemoveDataHandler
     */
    private $removeDataHandler;

    /**
     * UpdateDataHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param BuyableObjectResolver    $buyableObjectResolver
     * @param RemoveDataHandler        $removeDataHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BuyableObjectResolver $buyableObjectResolver,
        RemoveDataHandler $removeDataHandler
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->buyableObjectResolver = $buyableObjectResolver;
        $this->removeDataHandler     = $removeDataHandler;
    }

    /**
     * @param UpdateData $command
     */
    public function handle(UpdateData $command)
    {
        if ($command->object instanceof MediaCollection &&
            count($command->object->getMedias()) === 0
        ) {
            $this->removeDataHandler->handle(new RemoveData(
                $command->templateData,
                $command->object,
                $command->sheet
            ));
        }

        $this->buyableObjectResolver->updateCart($command->sheet, $command->object);
        $this->sheetRepository->set($command->sheet->setData($command->templateData->getData()));
    }
}
