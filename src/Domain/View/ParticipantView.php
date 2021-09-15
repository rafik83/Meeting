<?php

namespace Proximum\Vimeet\Domain\View;

class ParticipantView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $data;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $userEmail;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @param int    $id
     * @param string $data
     * @param int    $userId
     * @param string $userEmail
     * @param bool   $owner
     */
    public function __construct($id, $data, $userId, $userEmail, $owner)
    {
        $this->id        = $id;
        $this->data      = $data;
        $this->userId    = $userId;
        $this->userEmail = $userEmail;
        $this->owner     = $owner;
    }
}
