<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View;

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

    public function __construct(string $sheetTitle, array $sheetRegistrationData, array $participantRegistrationData)
    {
        $this->sheetTitle = $sheetTitle;
        $this->sheetRegistrationData = $sheetRegistrationData;
        $this->participantRegistrationData = $participantRegistrationData;
        $this->sheetTemplateData = [];
    }

    /**
     * @param array $sheetTemplateData
     */
    public function setSheetTemplateData(array $sheetTemplateData): void
    {
        $this->sheetTemplateData = $sheetTemplateData;
    }
}
