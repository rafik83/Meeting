<?php

namespace Proximum\Vimeet\Application\View\Sheet;

class ParticipantDataView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var array
     */
    public $rows;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @var bool
     */
    public $editable;

    /**
     * @var bool
     */
    public $deletable;

    /**
     * ParticpantView constructor.
     *
     * @param int    $id
     * @param string $email
     * @param array  $rows
     * @param bool   $owner
     * @param bool   $editable
     * @param bool   $deletable
     */
    public function __construct($id, $email, array $rows, $owner, $editable, $deletable)
    {
        $this->id        = $id;
        $this->email     = $email;
        $this->rows      = $rows;
        $this->owner     = $owner;
        $this->editable  = $editable;
        $this->deletable = $deletable;
    }
}
