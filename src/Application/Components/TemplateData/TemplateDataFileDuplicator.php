<?php

namespace Proximum\Vimeet\Application\Components\TemplateData;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;

class TemplateDataFileDuplicator
{
    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    public function handle(TemplateData $templateData): TemplateData
    {
        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof Image
                && !empty($object->getContentValue())
                && $object->hasTag(Tag::SHEET_LOGO)
            ) {
                $newFilePath = $this->fileStorage->copyAndRename($object->getContentValue());

                $object->setContentValue($newFilePath);
            }
        }

        return $templateData;
    }
}
