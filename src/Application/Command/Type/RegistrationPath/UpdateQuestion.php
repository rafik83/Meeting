<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Domain\Type\RegistrationPath\View\AnswerView;

class UpdateQuestion implements Command
{
    /** @var Question */
    public $question;

    /**
     * @var array
     * example: ['fr' => 'Ma question', 'en' => 'My question']
     */
    public $translatedTitle = [];

    /** @var AnswerView[] */
    public $answers = [];

    public function __construct(Question $question)
    {
        $this->question = $question;
        $event = $question->getEvent();

        foreach ($event->getLocales() as $locale) {
            $this->translatedTitle[$locale] = $question->getTitle($locale);
        }

        $answerViews = [];

        foreach ($question->getAnswers() as $answer) {
            $answerView = new AnswerView();

            foreach ($event->getLocales() as $locale) {
                $answerView->translatedTitle[$locale] = $answer->getTitle($locale);
            }

            $answerViews[] = $answerView;
        }

        $this->answers = $answerViews;
    }
}
