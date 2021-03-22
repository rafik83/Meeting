<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotAllowedException;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\Happening\QuestionVote;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionVoteRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class VoteHappeningQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var QuestionVoteRepositoryInterface */
    private $questionVoteRepository;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        QuestionVoteRepositoryInterface $questionVoteRepository,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->questionRepository = $questionRepository;
        $this->questionVoteRepository = $questionVoteRepository;
        $this->notificationPublisher = $notificationPublisher;
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
            $this->publishUpdate($question);

            return;
        }

        $questionVote = new QuestionVote(
            $question,
            $command->getUser()
        );

        $this->questionVoteRepository->add($questionVote);
        $this->publishUpdate($question);
    }

    private function publishUpdate(Question $question)
    {
        $this->notificationPublisher->publishHappeningNotification($question->getHappening(), AbstractNotification::TYPE_QUESTIONS, [
            'action' => 'vote',
        ]);
    }
}
