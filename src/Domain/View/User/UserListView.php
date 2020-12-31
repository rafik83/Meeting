<?php

namespace Proximum\Vimeet\Domain\View\User;

class UserListView
{
    /** @var int */
    public $userId;

    /** @var string */
    public $email;

    /** @var null|string */
    public $lastName;

    /** @var null|string */
    public $firstName;

    /** @var null|int */
    public $sheetId;

    /** @var null|int */
    public $sheetTypeId;

    /** @var null|string */
    private $typeTitle;

    /** @var null|string */
    private $sheetTypeTitle;

    /**
     * @param int         $userId
     * @param string      $email
     * @param null|string $lastName
     * @param null|string $firstName
     * @param null|string $typeTitle
     * @param null|int    $sheetId
     * @param null|int    $sheetTypeId
     * @param null|string $sheetTypeTitle
     */
    public function __construct(
        $userId,
        $email,
        $lastName,
        $firstName,
        $typeTitle,
        $sheetId,
        $sheetTypeId,
        $sheetTypeTitle
    ) {
        $this->userId         = $userId;
        $this->email          = $email;
        $this->lastName       = $lastName;
        $this->firstName      = $firstName;
        $this->typeTitle      = $typeTitle;
        $this->sheetId        = $sheetId;
        $this->sheetTypeId    = $sheetTypeId;
        $this->sheetTypeTitle = $sheetTypeTitle;
    }

    /**
     * @return null|string
     */
    public function getTypeTitle()
    {
        if (null !== $this->sheetTypeId) {
            return $this->sheetTypeTitle;
        }

        return $this->typeTitle;
    }
}
