<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ComexposiumWebservice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is for development purpose
 * I'll like to remove it, remove it.
 */
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
        $this->comexposiumWebservice->getEvents();
    }
}
