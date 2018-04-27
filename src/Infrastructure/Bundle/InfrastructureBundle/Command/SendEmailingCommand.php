<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Command\Messaging\Batch\Process;
use Proximum\Vimeet\Application\Command\Messaging\Batch\ProcessHandler;
use Proximum\Vimeet\Application\Components\Messaging\MessageFactory;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEmailingCommand extends Command
{
    const NAME     = 'vimeet:emailing:send';
    const BOOL_YES = 'YES';
    const BOOL_NO  = 'NO';

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var ProcessHandler
     */
    private $processHandler;

    /**
     * SendEmailingCommand constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param MessageFactory           $messageFactory
     * @param ProcessHandler           $processHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        MessageFactory $messageFactory,
        ProcessHandler $processHandler
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->messageFactory  = $messageFactory;
        $this->sheetRepository = $sheetRepository;
        $this->processHandler  = $processHandler;
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
            ->addArgument('sendEmailToTeam', InputArgument::OPTIONAL, 'Send email to team', self::BOOL_NO);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event           = $this->eventRepository->getById($input->getArgument('eventId'));
        $sheetIds        = explode(',', $input->getArgument('sheetIds'));
        $sheets          = $this->sheetRepository->findByIds($sheetIds);
        $messageId       = $input->getArgument('emailingId');
        $sendEmailToTeam = self::BOOL_YES === $input->getArgument('sendEmailToTeam') ? true : false;

        if (null === $event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $message = $this->messageFactory->create($event, $messageId, $sendEmailToTeam);

        $this->processHandler->handle(new Process($message, $sheets));
    }
}
