<?php

namespace Proximum\Vimeet\Application\View\Participant;

class ImportMappingView
{
    /**
     * Array of field headers column
     *
     * @var array
     */
    public $fieldHeaders;

    /**
     * Array of template registration block keys
     *
     * @var array
     */
    public $registrationHeaders;

    /** @var bool */
    public $allowMultiSheet;

    public function __construct(
        array $fieldHeaders,
        array $registrationHeaders,
        bool $allowMultiSheet
    ) {
        $this->fieldHeaders = $fieldHeaders;
        $this->registrationHeaders = $registrationHeaders;
        $this->allowMultiSheet = $allowMultiSheet;
    }
}
