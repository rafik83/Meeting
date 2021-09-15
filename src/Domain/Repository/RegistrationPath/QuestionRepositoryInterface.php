<?php

namespace Proximum\Vimeet\Domain\Repository\RegistrationPath;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;

interface QuestionRepositoryInterface
{
    public function add(Question $question): void;

    public function set(Question $question): void;

    public function remove(Question $question): void;

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Question[]
     */
    public function getQuestionsByEvent(Event $event, string $locale): array;
}
