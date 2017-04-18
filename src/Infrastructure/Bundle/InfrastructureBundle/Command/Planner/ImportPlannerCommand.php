<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\Planner\Import;
use Proximum\Vimeet\Application\Command\Planner\ImportHandler;
use Proximum\Vimeet\Application\Exception\Planner\Import\InvalidArgumentForImportException;
use Proximum\Vimeet\Application\Exception\Planner\InvalidXmlException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\Error\ImportPlannerMailError;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPlannerCommand extends Command
{
    const NAME = 'vimeet:planner:import';

    /** @var ImportHandler */
    private $importPlannerHandler;

    /** @var MailerInterface */
    private $mailer;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var string */
    private $mailSender;

    /**
     * ImportPlannerCommand constructor.
     *
     * @param ImportHandler            $importPlannerHandler
     * @param MailerInterface          $mailer
     * @param EventRepositoryInterface $eventRepository
     * @param string                   $mailSender
     */
    public function __construct(
        ImportHandler $importPlannerHandler,
        MailerInterface $mailer,
        EventRepositoryInterface $eventRepository,
        $mailSender
    ) {
        parent::__construct(self::NAME);

        $this->importPlannerHandler = $importPlannerHandler;
        $this->mailer               = $mailer;
        $this->eventRepository      = $eventRepository;
        $this->mailSender           = $mailSender;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Import xml planner file for algorithm')
            ->addArgument('file', InputArgument::REQUIRED, 'File id')
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
            ->addArgument('admin_email', InputArgument::REQUIRED, 'Admin email to notify')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();

        try {
            $this->importPlannerHandler->handle(
                new Import(
                    $arguments['file'],
                    $arguments['event'],
                    $arguments['admin_email'],
                    $arguments['locale']
                )
            );
        } catch (InvalidArgumentForImportException $exception) {
            $this->notifyAdminAboutError(
                $arguments['event'],
                $arguments['admin_email'],
                $arguments['locale'],
                $exception->getMessage()
            );

            return;
        } catch (InvalidXmlException $exception) {
            $this->notifyAdminAboutError(
                $arguments['event'],
                $arguments['admin_email'],
                $arguments['locale'],
                $exception->getMessage()
            );

            return;
        }
    }

    /**
     * @param int    $eventId
     * @param string $emailToNotify
     * @param string $locale
     * @param string $errorMessage
     */
    private function notifyAdminAboutError($eventId, $emailToNotify, $locale, $errorMessage)
    {
        $event = $this->eventRepository->getById($eventId);

        $this->mailer->send(new ImportPlannerMailError(
            $event,
            $this->mailSender,
            $emailToNotify,
            $locale,
            $errorMessage
        ));
    }
}
