<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\User;

class QuestionVote
{
    /** @var int */
    private $id;

    /** @var Question */
    private $question;

    /** @var User */
    private $user;

    public function __construct(Question $question, User $user)
    {
        $this->question = $question;
        $this->user = $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
