<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Command\Messaging\Batch\SendEmailingByType;
use Proximum\Vimeet\Application\Command\Messaging\Batch\SendEmailingByTypeHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEmailingCommand extends Command
{
    const NAME = 'vimeet:emailing:send';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SendEmailingByTypeHandler */
    private $sendEmailingByTypeHandler;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        SendEmailingByTypeHandler $sendEmailingByTypeHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->sheetRepository = $sheetRepository;
        $this->sendEmailingByTypeHandler = $sendEmailingByTypeHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Send emailing to a pull of sheets')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
            ->addArgument('emailingId', InputArgument::REQUIRED, 'Emailing ID')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));
        $sheetIds = explode(',', $input->getArgument('sheetIds'));
        $sheets = $this->sheetRepository->findByIds($sheetIds);
        $messageId = $input->getArgument('emailingId');

        if (null === $event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $this->sendEmailingByTypeHandler->handle(new SendEmailingByType($event, $messageId, $sheets));
    }
}
