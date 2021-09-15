<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\DeleteQuestionReplyNotAllowedException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\HappeningQuestionNotFound;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class DeleteHappeningQuestionReplyHandler
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

    public function handle(DeleteHappeningQuestionReply $command): void
    {
        $question = $this->questionRepository->findById($command->questionId);

        if (null === $question) {
            throw new HappeningQuestionNotFound(sprintf('Question #%d not found', $command->questionId));
        }

        if (!$question->getHappening()->hasSpeaker($command->user)) {
            throw new DeleteQuestionReplyNotAllowedException(sprintf('Deleting reply for question #%d is not allowed for this user', $command->questionId));
        }

        $question->deleteReply();
        $this->questionRepository->update($question);

        $this->notificationPublisher->publishHappeningNotification($question->getHappening(), AbstractNotification::TYPE_QUESTIONS, [
            'action' => 'delete',
            'delta' => -1,
        ]);
    }
}
