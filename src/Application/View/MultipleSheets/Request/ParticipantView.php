<?php

namespace Proximum\Vimeet\Application\View\MultipleSheets\Request;

class ParticipantView
{
    /**
     * @var string
     */
    public $completeName;

    /**
     * @param string $completeName
     */
    public function __construct($completeName)
    {
        $this->completeName = $completeName;
    }
}
