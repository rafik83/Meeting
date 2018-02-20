<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Template\TemplateData;

class SheetAndParticipantTemplateDataHandler
{
    public function handle(
        RegistrationView $registrationView,
        TemplateData $templateData
    ): SheetAndParticipantTemplateDataView {
        return new SheetAndParticipantTemplateDataView([], []);
    }
}
