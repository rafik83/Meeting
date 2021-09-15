<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Sheet\Phone;

use Proximum\Vimeet\Application\Command\Sheet\Phone\UpdatePhoneValidationStatus;
use Proximum\Vimeet\Application\Command\Sheet\Phone\UpdatePhoneValidationStatusHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PhoneValidationStatusCalculatorCommand extends Command
{
    const NAME = 'vimeet:sheet:phone-validation-status-calculator';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var UpdatePhoneValidationStatusHandler */
    private $updatePhoneValidationStatusHandler;

    /**
     * @param EventRepositoryInterface           $eventRepository
     * @param UpdatePhoneValidationStatusHandler $updatePhoneValidationStatusHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        UpdatePhoneValidationStatusHandler $updatePhoneValidationStatusHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->updatePhoneValidationStatusHandler = $updatePhoneValidationStatusHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Calculate the phone validation status for the sheets of the event')
            ->addOption(
                'event',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the phone validation status will be calculate only for the sheet of the given event id'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = $input->getOption('event');

        if (null !== $eventId) {
            $event = $this->eventRepository->getById($eventId);

            if (null === $event) {
                $output->writeln(sprintf('The event for the id %s was not found', $eventId));

                return;
            }

            $output->writeln(
                sprintf(
                    'Calculating phone validation status for the event %s with id %s',
                    $event->getTitle(),
                    $eventId
                )
            );
            $this->updatePhoneValidationStatusHandler->handle(new UpdatePhoneValidationStatus($event));

            return;
        }

        $events = $this->eventRepository->getAll();

        foreach ($events as $event) {
            $output->writeln(
                sprintf(
                    'Calculating phone validation status for the event %s with id %s',
                    $event->getTitle(),
                    $eventId
                )
            );
            $this->updatePhoneValidationStatusHandler->handle(new UpdatePhoneValidationStatus($event));
        }
    }
}
