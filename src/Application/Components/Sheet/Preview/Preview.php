<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class Preview
{
    /**
     * @var TemplateDataFactory
     */
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
     * @param string $locale
     *
     * @return TemplateObject[]
     */
    public function getPreview(Sheet $sheet, $locale)
    {
        $previewObjectKeys = $sheet->getTypeSheetTemplate()->getPreview();
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);

        $previewObjects = [];

        foreach ($previewObjectKeys as $key) {
            $previewObjects[] = $templateData->getObject($key);
        }

        return $previewObjects;
    }
}
