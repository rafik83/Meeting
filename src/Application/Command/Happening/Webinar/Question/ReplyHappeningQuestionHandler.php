<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\ReplyQuestionNotAllowedException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\HappeningQuestionNotFound;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class ReplyHappeningQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    /** @var DateTimeInterface */
    private $datetime;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        NotificationPublisherInterface $notificationPublisher,
        DateTimeInterface $datetime
    ) {
        $this->questionRepository = $questionRepository;
        $this->notificationPublisher = $notificationPublisher;
        $this->datetime = $datetime;
    }

    public function handle(ReplyHappeningQuestion $command): void
    {
        if ('' === trim($command->replyContent)) {
            return;
        }

        $question = $this->questionRepository->findById($command->questionId);

        if (null === $question) {
            throw new HappeningQuestionNotFound(sprintf('Question #%d not found', $command->questionId));
        }

        if (!$question->getHappening()->hasSpeaker($command->repliedBy)) {
            throw new ReplyQuestionNotAllowedException(sprintf('Replying to question #%d is not allowed for this user', $command->questionId));
        }

        if (null !== $question->getRepliedBy() && $question->getRepliedBy() !== $command->repliedBy) {
            throw new ReplyQuestionNotAllowedException('Updating reply to question is allowed only for author of reply');
        }

        $question->setReply($command->replyContent, $command->repliedBy, $this->datetime);
        $this->questionRepository->update($question);

        $this->notificationPublisher->publishHappeningNotification($question->getHappening(), AbstractNotification::TYPE_QUESTIONS, [
            'action' => 'update',
            'msg_count' => $this->questionRepository->getMessagesCountDuringHappening($question->getHappening()),
        ]);
    }
}
