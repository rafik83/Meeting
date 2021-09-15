<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\Sheet;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Event\Sheet\Index as SheetIndex;
use Proximum\Vimeet\Application\Command\UserEventView\Index as UserEventViewIndex;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexSheetsByEventCommand extends Command
{
    const NAME = 'vimeet:event:index-sheets';

    public const RESET = 'reset';
    public const NO_RESET = 'no-reset';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param CommandBusInterface      $commandBus
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        CommandBusInterface $commandBus
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->commandBus      = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index the sheet for the given event id')
            ->addArgument(
                'eventId',
                InputArgument::REQUIRED,
                'The id of the event which the sheets will be re-index for'
            )
            ->addArgument(
                'reset',
                InputArgument::REQUIRED,
                'Argument to reset all the entries of the event first'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));
        $reset = self::RESET === $input->getArgument('reset');

        if (null === $event) {
            throw new \Exception('Event not found.');
        }

        $output->writeln(sprintf(
            'Start the indexation of the sheets of the event %d %s%s',
            $event->getId(),
            $event->getTitle(),
            $reset ? ' with reset of the event first.' : '.'
        ));

        $this->commandBus->handle(new SheetIndex($event, $reset));
        $this->commandBus->handle(new UserEventViewIndex($event, $reset));

        $output->writeln('Indexation finished.');

        return 0;
    }
}
