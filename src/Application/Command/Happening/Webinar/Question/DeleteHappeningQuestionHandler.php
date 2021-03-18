<?php


namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\DeleteQuestionNotAllowedException;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotFoundException;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class DeleteHappeningQuestionHandler
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

    public function handle(DeleteHappeningQuestion $command): void
    {
        if (!$command->happening->hasSpeaker($command->user)) {
            throw new DeleteQuestionNotAllowedException(sprintf('Delete question #%d is not allowed for this user', $command->messageId));
        }

        $question = $this->questionRepository->findById($command->messageId);

        if (null === $question) {
            throw new QuestionNotFoundException(sprintf('Question with ID #%d not found', $command->messageId));
        }

        $this->notificationPublisher->publishHappeningNotification($command->happening, AbstractNotification::TYPE_QUESTIONS, [
            'action' => 'delete',
            'delta' => $question->getReplyContent() ? -2 : -1,
        ]);

        $this->questionRepository->delete($question);
    }
}
