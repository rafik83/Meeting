<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View;

class SheetAndParticipantTemplateDataView
{
    /** @var array */
    public $sheetTemplateData;

    /** @var array */
    public $participantTemplateData;

    public function __construct(array $sheetTemplateData, array $participantTemplateData)
    {
        $this->sheetTemplateData = $sheetTemplateData;
        $this->participantTemplateData = $participantTemplateData;
    }
}
