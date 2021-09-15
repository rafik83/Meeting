<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group;

class ImpersonationUserView
{
    /** @var string */
    public $fromEmail;

    /** @var string */
    public $toEmail;

    /** @var string */
    public $toFirstName;

    /** @var string */
    public $toLastName;

    /** @var int */
    public $sheetGroupId;

    /**
     * @param string $fromEmail
     * @param string $toEmail
     * @param string $toFirstName
     * @param string $toLastName
     * @param int    $sheetGroupId
     */
    public function __construct($fromEmail, $toEmail, $toFirstName, $toLastName, $sheetGroupId)
    {
        $this->fromEmail = $fromEmail;
        $this->toEmail = $toEmail;
        $this->toFirstName = $toFirstName;
        $this->toLastName = $toLastName;
        $this->sheetGroupId = $sheetGroupId;
    }
}
