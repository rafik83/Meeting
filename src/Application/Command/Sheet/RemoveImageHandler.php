<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class RemoveImageHandler
{
    /**
     * @var LocalFileStorageAdapter
     */
    private $localFileStorageAdapter;

    /**
     * @var RemoveDataHandler
     */
    private $removeDataHandler;

    /**
     * RemoveImageHandler constructor.
     *
     * @param LocalFileStorageAdapter $localFileStorageAdapter
     * @param RemoveDataHandler       $removeDataHandler
     */
    public function __construct(
        LocalFileStorageAdapter $localFileStorageAdapter,
        RemoveDataHandler $removeDataHandler
    ) {
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->removeDataHandler       = $removeDataHandler;
    }

    /**
     * @param RemoveImage $removeImage
     */
    public function handle(RemoveImage $removeImage)
    {
        $imagePath = $removeImage->image->getImage();

        // remove data from sheet
        $this->removeDataHandler->handle(new RemoveData(
            $removeImage->templateData,
            $removeImage->image,
            $removeImage->sheet
        ));

        // remove binary file
        $this->localFileStorageAdapter->remove($imagePath);
    }
}
