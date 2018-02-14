<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComexposiumGetRegistrationCommand extends Command
{
    public const NAME = 'vimeet:comexposium:get-registration';

    private const EVENT_ID = 'eventId';
    private const REGISTRATION_REFERENCE = 'registrationReference';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
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
            ->addArgument(self::REGISTRATION_REFERENCE, InputArgument::REQUIRED, 'Registration reference');
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

        // @todo: getRegistration->handle($event, $registrationReference)
    }
}
