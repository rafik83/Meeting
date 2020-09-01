<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\WebinarNotificationPublisherInterface;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractWebinarNotification;

class AddHappeningQuestionHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var WebinarNotificationPublisherInterface */
    private $webinarNotificationPublisher;

    /** @var DateTimeInterface */
    private $datetime;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        WebinarNotificationPublisherInterface $webinarNotificationPublisher,
        DateTimeInterface $datetime
    ) {
        $this->questionRepository = $questionRepository;
        $this->webinarNotificationPublisher = $webinarNotificationPublisher;
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

        $this->webinarNotificationPublisher->send($command->getHappening(), AbstractWebinarNotification::TYPE_QUESTIONS, [
            'action' => 'update',
            'authorId' => $command->getCreatedBy()->getId(),
        ]);
    }
}
