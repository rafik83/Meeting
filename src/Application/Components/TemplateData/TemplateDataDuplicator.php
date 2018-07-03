<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\TemplateData;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class TemplateDataDuplicator
{
    /** @var TemplateDataFactory */
    private $factory;

    /** @var TemplateDataFileDuplicator */
    private $fileDuplicator;

    public function __construct(
        TemplateDataFactory $factory,
        TemplateDataFileDuplicator $fileDuplicator
    ) {
        $this->factory = $factory;
        $this->fileDuplicator = $fileDuplicator;
    }

    /**
     * @param Sheet      $sheet
     * @param null|Sheet $fromSheet if provided, the template of the fromSheet will be used for object with tags
     * @param array      $sanitizedTypesOfObject array of type of object to remove from template (media, etc...)
     *
     * @return Sheet
     */
    public function duplicateData(
        Sheet $sheet,
        ?Sheet $fromSheet = null,
        array $sanitizedTypesOfObject = []
    ): Sheet {
        $fromSheetTagsData = [];

        if ($fromSheet !== null) {
            $fromSheetTemplateData = $this->factory->createFromSheet($fromSheet, null);

            foreach ($fromSheetTemplateData->getObjects() as $key => $object) {
                $tags = $object->getTags();

                if (empty($tags)) {
                    continue;
                }

                foreach ($tags as $tag) {
                    if (\in_array($tag, Tag::SHEET_TEMPLATE_TAGS, true) && !isset($fromSheetTagsData[$tag])) {
                        $fromSheetTagsData[$tag] = $object->getData();
                    }
                }
            }
        }

        $templateData = $this->factory->createFromSheet($sheet, null);

        if (!empty($fromSheetTagsData)) {
            foreach ($templateData->getObjects() as $object) {
                $tags = $object->getTags();

                if (empty($tags)) {
                    continue;
                }

                foreach ($tags as $tag) {
                    if (\in_array($tag, Tag::SHEET_TEMPLATE_TAGS, true) && isset($fromSheetTagsData[$tag])) {
                        $object->setData($fromSheetTagsData[$tag]);
                    }
                }
            }
        }

        if (!empty($sanitizedTypesOfObject)) {
            $templateData->sanitizedDataWithoutType($sanitizedTypesOfObject);
        }

        $templateData = $this->fileDuplicator->handle($templateData);

        $sheet->setData($templateData->getData());

        return $sheet;
    }
}
