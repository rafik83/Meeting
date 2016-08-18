<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

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
     * RemoveImageHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param LocalFileStorageAdapter  $localFileStorageAdapter
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        LocalFileStorageAdapter $localFileStorageAdapter
    ) {
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->sheetRepository         = $sheetRepository;
    }

    /**
     * @param RemoveImage $removeImage
     */
    public function handle(RemoveImage $removeImage)
    {
        $imagePath = $removeImage->image->getImage();
        $removeImage->image->setData([]);

        $this->sheetRepository->set($removeImage->sheet->setData($removeImage->templateData->getData()));
        $this->localFileStorageAdapter->remove($imagePath);
    }
}
