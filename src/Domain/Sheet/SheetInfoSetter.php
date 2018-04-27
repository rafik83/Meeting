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

class SheetInfoSetter
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Sheet  $sheet
     * @param string $title
     */
    public function setSheetTitle(Sheet $sheet, $title)
    {
        $templateData = $this->templateDataFactory->createRegistrationFromSheet($sheet);

        foreach ($templateData->getEditableSheetDataExceptedImageObjects() as $object) {
            if ($object->hasTag(Template\Tag::SHEET_TITLE) && $object instanceof ContentObjectInterface) {
                $object->setContentValue($title);
            }
        }

        $sheet->setRegistrationData($templateData->getSheetData());
    }
}
