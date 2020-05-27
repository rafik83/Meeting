<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Template;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;

class SheetInfoSetter
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Sheet  $sheet
     * @param string $title
     */
    public function setSheetTitle(Sheet $sheet, $title): void
    {
        $templateData = $this->templateDataFactory->createRegistrationFromSheet($sheet);

        foreach ($templateData->getEditableSheetDataExceptedImageObjects() as $object) {
            if ($object->hasTag(Template\Tag::SHEET_TITLE) && $object instanceof ContentObjectInterface) {
                $object->setContentValue($title);
            }
        }

        $sheet->setRegistrationData($templateData->getSheetData());
        $this->removePreviousSheetTitleInTemplateObjects($sheet);
    }

    private function removePreviousSheetTitleInTemplateObjects(Sheet $sheet): void
    {
        $sheetTemplateData = $this->templateDataFactory->createFromSheet($sheet);

        foreach ($sheetTemplateData->getObjects() as $object) {
            if ($object instanceof ContentObjectInterface
                && in_array($object->getTag(), [Template\Tag::SHEET_TITLE, Template\Tag::SHEET_ORGANIZATION], true)
            ) {
                $object->setContentValue(null);

                if ($object instanceof EditableText) {
                    $object->eraseData();
                }
            }
        }

        $sheet->setData($sheetTemplateData->getData());
    }
}
