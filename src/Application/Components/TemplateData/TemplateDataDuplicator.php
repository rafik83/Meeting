<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\TemplateData;

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

    public function duplicateData(Sheet $sheet): Sheet
    {
        $templateData = $this->factory->createFromSheet($sheet, null);
        $templateData = $this->fileDuplicator->handle($templateData);

        $sheet->setData($templateData->getData());

        return $sheet;
    }
}
