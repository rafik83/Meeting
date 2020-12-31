<?php

namespace Proximum\Vimeet\Application\View\Catalog\Export;

class SheetView
{
    /** @var string */
    public $typeTitle;

    /** @var array */
    public $sheetRegistrationInfo;

    /** @var array */
    public $sheetInfo;

    /** @var string */
    public $participantPosition;

    /**
     * @param string $typeTitle
     * @param array  $sheetRegistrationInfo
     * @param array  $sheetInfo
     * @param string $participantPosition
     */
    public function __construct(
        string $typeTitle = '',
        array $sheetRegistrationInfo,
        array $sheetInfo,
        string $participantPosition = ''
    ) {
        $this->typeTitle = $typeTitle;
        $this->sheetRegistrationInfo = $sheetRegistrationInfo;
        $this->sheetInfo = $sheetInfo;
        $this->participantPosition = $participantPosition;
    }

    /**
     * @param array $sheetRegistrationFields
     */
    public function reMapRegistrationFields(array &$sheetRegistrationFields)
    {
        foreach ($sheetRegistrationFields as $key => $registrationField) {
            if (!isset($this->sheetRegistrationInfo[$key])) {
                $this->sheetRegistrationInfo[$key] = '';
            }
        }
    }

    /**
     * @param array $sheetFields
     */
    public function reMapFields(array &$sheetFields)
    {
        foreach ($sheetFields as $key => $field) {
            if (!isset($this->sheetInfo[$key])) {
                $this->sheetInfo[$key] = '';
            }
        }
    }
}
