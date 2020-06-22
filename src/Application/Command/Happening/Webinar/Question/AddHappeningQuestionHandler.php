<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class AddHappeningQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        \DateTimeInterface $datetime
    ) {
        $this->questionRepository = $questionRepository;
        $this->datetime = $datetime;
    }

    public function handle(AddHappeningQuestion $command): void
    {
        if ('' === trim($command->getContent())) {
            return;
        }

        $question = new Question(
            $command->getHappening(),
            $command->getSheet(),
            $command->getCreatedBy(),
            $this->datetime,
            $command->getContent(),
            true
        );

        $this->questionRepository->add($question);
    }
}
