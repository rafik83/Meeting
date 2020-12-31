<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\View;

class LeaderView
{
    /** @var string */
    public $leniUserId;

    /** @var null|string */
    public $firstName;

    /** @var null|string */
    public $lastName;

    /** @var null|string */
    public $sheetName;

    /** @var string */
    public $email;

    public function __construct(
        string $leniUserId,
        string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $sheetName
    ) {
        $this->leniUserId = $leniUserId;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetName = $sheetName;
    }
}
