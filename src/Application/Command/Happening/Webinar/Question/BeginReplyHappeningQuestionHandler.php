<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\BeginReplyHappeningQuestion;
use Proximum\Vimeet\Application\Exception\Happening\ReplyQuestionNotAllowedException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\HappeningQuestionNotFound;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class BeginReplyHappeningQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->questionRepository = $questionRepository;
        $this->notificationPublisher = $notificationPublisher;
    }

    public function handle(BeginReplyHappeningQuestion $command): void
    {
        $question = $this->questionRepository->findById($command->questionId);

        if (null === $question) {
            throw new HappeningQuestionNotFound(sprintf('Question #%d not found', $command->questionId));
        }

        if (!$question->getHappening()->hasSpeaker($command->repliedBy)) {
            throw new ReplyQuestionNotAllowedException(sprintf('Replying to question #%d is not allowed for this user', $command->questionId));
        }

        $this->notificationPublisher->publishHappeningNotification($question->getHappening(), AbstractNotification::TYPE_QUESTIONS, [
            'action' => 'begin_reply',
            'questionId' => $command->questionId,
            'author' => $command->repliedBy->getFullname(),
            'authorId' => $command->repliedBy->getId(),
        ]);
    }
}
