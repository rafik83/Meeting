<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class AddHappeningQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        \DateTimeInterface $datetime
    ) {
        $this->questionRepository = $questionRepository;
        $this->datetime = $datetime;
    }

    public function handle(AddHappeningQuestion $command)
    {
        $question = new Question($command->getHappening(), $command->getSheet(), $command->getCreatedBy(), $this->datetime, $command->getContent());

        $this->questionRepository->add($question);
    }
}
