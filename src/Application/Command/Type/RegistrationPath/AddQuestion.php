<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Type\RegistrationPath\View\AnswerView;

class AddQuestion implements Command
{
    /** @var Event */
    public $event;

    /**
     * @var array
     * example: ['fr' => 'Ma question', 'en' => 'My question']
     */
    public $translatedTitle = [];

    /** @var AnswerView[] */
    public $answers = [];

    /** @var Answer|null */
    public $previousAnswer;

    public function __construct(Event $event, ?Answer $previousAnswer)
    {
        $this->event = $event;
        $this->previousAnswer = $previousAnswer;

        // create by default two empties answers
        $this->answers = [new AnswerView(), new AnswerView()];
    }
}
