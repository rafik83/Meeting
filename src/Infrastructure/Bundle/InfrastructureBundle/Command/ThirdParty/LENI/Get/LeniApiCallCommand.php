<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\LENI\Get;

use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCallHandler;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LeniApiCallCommand extends Command
{
    const NAME = 'vimeet:api:leni-get-call';
    const EXTRA_DATA_ID = 'extraDataId';

    /** @var LeniApiCallHandler */
    private $leniApiCallHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param LeniApiCallHandler           $leniApiCallHandler
     * @param ExtraDataRepositoryInterface $extraDataRepository
     *
     * @throws \LogicException
     */
    public function __construct(
        LeniApiCallHandler $leniApiCallHandler,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        parent::__construct(self::NAME);

        $this->leniApiCallHandler = $leniApiCallHandler;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Call the LENI API to get users for an event')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LeniException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->leniApiCallHandler->handle(new LeniApiCall());
        } catch (\Exception $leniException) {
            throw new LeniException($leniException->getMessage(), $leniException->getCode(), $leniException);
        }
    }
}
