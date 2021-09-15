<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\LENI\Save;

use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\PrepareLeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\PrepareLeniApiCallHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareLeniApiCallCommand extends Command
{
    const NAME = 'vimeet:api:leni-export-data';

    /** @var PrepareLeniApiCallHandler */
    private $apiHandler;

    /**
     * @param PrepareLeniApiCallHandler $apiHandler
     */
    public function __construct(PrepareLeniApiCallHandler $apiHandler)
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
        $this->apiHandler->handle(new PrepareLeniApiCall());
    }
}
