<?php

namespace Proximum\Vimeet\Application\View\Agenda;

class ParticipantView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $fullName;

    /**
     * @param int    $id
     * @param string $fullName
     */
    public function __construct($id, $fullName)
    {
        $this->id       = $id;
        $this->fullName = $fullName;
    }
}
