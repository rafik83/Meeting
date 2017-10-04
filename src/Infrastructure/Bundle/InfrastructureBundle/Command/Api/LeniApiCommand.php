<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Api;

use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Api\LeniApi;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Api\LeniApiHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LeniApiCommand extends Command
{
    const NAME = 'vimeet:api:leni-export-data';

    /** @var LeniApiHandler */
    private $apiHandler;

    /**
     * @param LeniApiHandler $apiHandler
     */
    public function __construct(LeniApiHandler $apiHandler)
    {
        parent::__construct(self::NAME);

        $this->apiHandler = $apiHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Send to the LENI API the data of the users for the event with LENI Extra Parameter')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->apiHandler->handle(new LeniApi());
    }
}
