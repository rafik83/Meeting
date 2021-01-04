<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\User\Agenda\Version;

use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\RecurrentNotificationOfChangedInVersionCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\RecurrentNotificationOfChangedInVersionCommandHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyVersionDiffDDayCommand extends Command
{
    private const NAME = 'vimeet:user:notify-agenda-version-diff-dday';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var RecurrentNotificationOfChangedInVersionCommandHandler */
    private $recurrentNotificationOfChangedInVersionCommandHandler;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        RecurrentNotificationOfChangedInVersionCommandHandler $recurrentNotificationOfChangedInVersionCommandHandler,
        \DateTimeInterface $dateTime
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->dateTime = $dateTime;
        $this->recurrentNotificationOfChangedInVersionCommandHandler = $recurrentNotificationOfChangedInVersionCommandHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Notify the users of version diff in their agenda for today\'s events')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $events = $this->eventRepository->findByDay($this->dateTime);

        $this->recurrentNotificationOfChangedInVersionCommandHandler->handle(
            new RecurrentNotificationOfChangedInVersionCommand($events, true)
        );
    }
}
