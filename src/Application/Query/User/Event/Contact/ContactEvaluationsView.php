<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

class ContactEvaluationsView
{
    /** @var int */
    private $userId;

    /** @var int */
    private $contactsNumber = 0;

    /** @var array */
    private $contactsNumberByEvaluations = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

    /** @var int */
    private $contactsNumberNotEvaluated = 0;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function addContact(?int $evaluation): void
    {
        ++$this->contactsNumber;

        if (null !== $evaluation) {
            ++$this->contactsNumberByEvaluations[$evaluation];

            return;
        }

        ++$this->contactsNumberNotEvaluated;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getContactsNumber(): int
    {
        return $this->contactsNumber;
    }

    public function getContactsNumberByEvaluation(int $evaluation): int
    {
        return $this->contactsNumberByEvaluations[$evaluation];
    }

    public function getContactsNumberNotEvaluated(): int
    {
        return $this->contactsNumberNotEvaluated;
    }

    public function getContactsNumberEvaluated(): int
    {
        return $this->contactsNumber - $this->contactsNumberNotEvaluated;
    }
}
