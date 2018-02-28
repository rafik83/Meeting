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
    /** @var string */
    public $sheetTitle;

    /** @var array */
    public $sheetRegistrationData;

    /** @var array */
    public $participantRegistrationData;

    public function __construct(string $sheetTitle, array $sheetRegistrationData, array $participantRegistrationData)
    {
        $this->sheetTitle = $sheetTitle;
        $this->sheetRegistrationData = $sheetRegistrationData;
        $this->participantRegistrationData = $participantRegistrationData;
    }
}
