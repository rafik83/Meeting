<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ComexposiumWebservice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComexposiumWebserviceCallCommand extends Command
{
    const NAME = 'vimeet:comexposium:call';

    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /**
     * @param ComexposiumWebservice $comexposiumWebservice
     */
    public function __construct(ComexposiumWebservice $comexposiumWebservice)
    {
        parent::__construct(self::NAME);

        $this->comexposiumWebservice = $comexposiumWebservice;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Call the Comexposium webservice')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$response = $this->comexposiumWebservice->getRegistration('13389', '2262768');
        //$response = $this->comexposiumWebservice->getRegistrationsReference('13389');
        $response = $this->comexposiumWebservice->getRegistrations('13389', ['2201249', '2276270']);
        dump($response);
    }
}
