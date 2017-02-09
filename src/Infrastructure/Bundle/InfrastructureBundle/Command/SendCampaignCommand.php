<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\Send;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SendHandler;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendCampaignCommand extends Command
{
    const NAME = 'vimeet:campaign:send';

    /**
     * @var CampaignRepositoryInterface
     */
    private $repository;

    /**
     * @var SendHandler
     */
    private $handler;

    /**
     * SendCampaignCommand constructor.
     *
     * @param CampaignRepositoryInterface $repository
     * @param SendHandler                 $handler
     */
    public function __construct(CampaignRepositoryInterface $repository, SendHandler $handler)
    {
        parent::__construct(self::NAME);

        $this->repository = $repository;
        $this->handler    = $handler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Build guideline asset for the events')
            ->addArgument('id', InputArgument::REQUIRED, 'Campaign id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return;

        $campaign = $this->repository->getById($input->getArgument('id'));

        if (null === $campaign) {
            throw new \Exception('Campaign not found.');
        }

        $this->handler->handle(new Send($campaign));
    }
}
