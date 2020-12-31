<?php

namespace Proximum\Vimeet\Domain\View;

class TypeTemplatesView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $participantTemplate;

    /**
     * @var string
     */
    public $sheetTemplate;

    /**
     * @var string
     */
    public $packageTemplate;

    /**
     * @param int    $id
     * @param string $participantTemplate
     * @param string $sheetTemplate
     * @param string $packageTemplate
     */
    public function __construct($id, $participantTemplate, $sheetTemplate, $packageTemplate)
    {
        $this->id                  = $id;
        $this->participantTemplate = $participantTemplate;
        $this->sheetTemplate       = $sheetTemplate;
        $this->packageTemplate     = $packageTemplate;
    }
}
