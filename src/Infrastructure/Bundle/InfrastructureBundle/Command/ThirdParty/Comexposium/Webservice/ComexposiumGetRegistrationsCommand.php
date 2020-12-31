<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ImportSheetsHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComexposiumGetRegistrationsCommand extends Command
{
    public const NAME = 'vimeet:comexposium:get-registrations';

    private const EVENT_ID = 'eventId';
    private const REGISTRATION_REFERENCES = 'registrationReferences';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ImportSheetsHandler */
    private $importSheetsHandler;

    public function __construct(EventRepositoryInterface $eventRepository, ImportSheetsHandler $importSheetsHandler)
    {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->importSheetsHandler = $importSheetsHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Get Comexposium Registration')
            ->addArgument(self::EVENT_ID, InputArgument::REQUIRED, 'Event id')
            ->addArgument(
                self::REGISTRATION_REFERENCES,
                InputArgument::REQUIRED,
                'Registration references separated by a comma'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument(self::EVENT_ID));

        if (null === $event) {
            throw new \InvalidArgumentException('Event not found');
        }

        $this->importSheetsHandler->handle($event, explode(',', $input->getArgument(self::REGISTRATION_REFERENCES)));
    }
}
