<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

class AnswerView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    public function __construct(int $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }
}
