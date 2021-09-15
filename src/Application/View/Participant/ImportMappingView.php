<?php

namespace Proximum\Vimeet\Application\View\Participant;

use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;

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

    /** @var ImportMapping|null */
    public $savedImportMapping;

    public function __construct(
        array $fieldHeaders,
        array $registrationHeaders,
        bool $allowMultiSheet,
        ?ImportMapping $savedImportMapping
    ) {
        $this->fieldHeaders = $fieldHeaders;
        $this->registrationHeaders = $registrationHeaders;
        $this->allowMultiSheet = $allowMultiSheet;
        $this->savedImportMapping = $savedImportMapping;
    }
}
