<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class AddHappeningQuestionCommandHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(
        QuestionRepositoryInterface $questionRepository
    ) {
        $this->questionRepository = $questionRepository;
    }

    public function handle(AddHappeningQuestionCommand $command)
    {
        $question = new Question($command->getHappening(), $command->getSheet(), $command->getCreatedBy(), new \DateTime(), $command->getContent());

        $this->questionRepository->add($question);
    }
}
