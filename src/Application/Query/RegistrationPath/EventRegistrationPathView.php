<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

class EventRegistrationPathView
{
    /** @var QuestionView|null */
    public $questionView;

    public function __construct(?QuestionView $questionView)
    {
        $this->questionView = $questionView;
    }
}
