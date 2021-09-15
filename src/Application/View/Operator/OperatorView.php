<?php

namespace Proximum\Vimeet\Application\View\Operator;

use Proximum\Vimeet\Domain\Model\Admin;

class OperatorView
{
    /** @var int */
    public $id;

    /** @var string */
    public $lastName;

    /** @var string */
    public $firstName;

    /** @var string */
    public $email;

    /** @var string[] */
    public $events;

    /** @var null|\DateTimeInterface */
    public $lastLoginAt;

    /** @var string */
    private $role;

    public function __construct(
        int $id,
        string $lastName,
        string $firstName,
        string $email,
        string $role,
        array $events,
        ?\DateTimeInterface $lastLoginAt = null
    ) {
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->events = $events;
        $this->lastLoginAt = $lastLoginAt;
        $this->role = $role;
    }

    public function isPartner(): bool
    {
        return $this->role === Admin::ROLE_PARTNER;
    }

    public function hasNeverLoggedIn(): bool
    {
        return $this->lastLoginAt === null;
    }
}
