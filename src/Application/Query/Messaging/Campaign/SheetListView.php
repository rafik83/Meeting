<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

class SheetListView
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $ownerEmail;

    /**
     * @param int    $id
     * @param string $name
     * @param string $ownerEmail
     */
    public function __construct($id, $name, $ownerEmail)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->ownerEmail = $ownerEmail;
    }
}
