<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
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

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
