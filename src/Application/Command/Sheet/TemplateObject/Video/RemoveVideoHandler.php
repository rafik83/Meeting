<?php

namespace Proximum\Vimeet\Application\Command\Sheet\TemplateObject\Video;

use Proximum\Vimeet\Application\Adapter\VideoStorageInterface;
use Proximum\Vimeet\Application\Command\Sheet\RemoveData;
use Proximum\Vimeet\Application\Command\Sheet\RemoveDataHandler;

class RemoveVideoHandler
{
    /** @var RemoveDataHandler */
    private $removeDataHandler;

    /** @var VideoStorageInterface */
    private $videoStorage;

    public function __construct(
        RemoveDataHandler $removeDataHandler,
        VideoStorageInterface $videoStorage
    ) {
        $this->removeDataHandler = $removeDataHandler;
        $this->videoStorage = $videoStorage;
    }

    public function handle(RemoveVideo $removeVideo): void
    {
        $videoPath = $removeVideo->video->getPath();

        if (empty($videoPath)) {
            return;
        }

        // remove data from sheet
        $this->removeDataHandler->handle(new RemoveData(
            $removeVideo->templateData,
            $removeVideo->video,
            $removeVideo->sheet
        ));

        $this->videoStorage->remove($videoPath);
    }
}
