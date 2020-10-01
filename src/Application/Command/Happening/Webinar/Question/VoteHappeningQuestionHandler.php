<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Exception\Happening\QuestionNotAllowedException;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening\QuestionVote;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionVoteRepositoryInterface;

class VoteHappeningQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var QuestionVoteRepositoryInterface */
    private $questionVoteRepository;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        QuestionVoteRepositoryInterface $questionVoteRepository
    ) {
        $this->questionRepository = $questionRepository;
        $this->questionVoteRepository = $questionVoteRepository;
    }

    public function handle(VoteHappeningQuestion $command): void
    {
        $question = $this->questionRepository->findById($command->getQuestionId());

        if (null === $question) {
            throw new QuestionNotFoundException();
        }

        if ($command->getUser()->getId() === $question->getCreatedBy()->getId()) {
            throw new QuestionNotAllowedException();
        }

        $questionVote = $this->questionVoteRepository->getByQuestionAndUser($question, $command->getUser());

        if ($questionVote) {
            $this->questionVoteRepository->remove($questionVote);

            return;
        }

        $questionVote = new QuestionVote(
            $question,
            $command->getUser()
        );

        $this->questionVoteRepository->add($questionVote);
    }
}
