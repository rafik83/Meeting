<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class AddHappeningQuestionHandler
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

        $this->notificationPublisher->publishHappeningNotification($command->getHappening(), AbstractNotification::TYPE_QUESTIONS, [
            'action' => 'update',
            'authorId' => $command->getCreatedBy()->getId(),
        ]);
    }
}
