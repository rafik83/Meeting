<?php

namespace Proximum\Vimeet\Application\View\Participant;

class SheetAndParticipantTemplateDataView
{
    /** @var string */
    public $sheetTitle;

    /** @var array */
    public $sheetRegistrationData;

    /** @var array */
    public $participantRegistrationData;

    /** @var array */
    public $sheetTemplateData;

    public function __construct(
        string $sheetTitle,
        array $sheetRegistrationData = [],
        array $participantRegistrationData = [],
        array $sheetTemplateData = []
    ) {
        $this->sheetTitle = $sheetTitle;
        $this->sheetRegistrationData = $sheetRegistrationData;
        $this->participantRegistrationData = $participantRegistrationData;
        $this->sheetTemplateData = $sheetTemplateData;
    }
}
