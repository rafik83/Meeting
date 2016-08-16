<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class RemoveImageHandler
{
    /**
     * @var LocalFileStorageAdapter
     */
    private $localFileStorageAdapter;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var BuyableObjectResolver
     */
    private $buyableObjectResolver;

    /**
     * RemoveImageHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param LocalFileStorageAdapter  $localFileStorageAdapter
     * @param BuyableObjectResolver    $buyableObjectResolver
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        LocalFileStorageAdapter $localFileStorageAdapter,
        BuyableObjectResolver $buyableObjectResolver
    ) {
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->sheetRepository         = $sheetRepository;
        $this->buyableObjectResolver   = $buyableObjectResolver;
    }

    /**
     * @param RemoveImage $removeImage
     */
    public function handle(RemoveImage $removeImage)
    {
        $this->buyableObjectResolver->removeImage($removeImage->sheet, $removeImage->image);

        $imagePath = $removeImage->image->getImage();
        $removeImage->image->setData([]);

        $this->sheetRepository->set($removeImage->sheet->setData($removeImage->templateData->getData()));
        $this->localFileStorageAdapter->remove($imagePath);
    }
}
