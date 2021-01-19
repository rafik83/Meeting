<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Vianeo;

use Proximum\Vimeet\Application\ThirdParty\Vianeo\Command\VianeoPrepareApiCall;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Command\VianeoPrepareApiCallHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VianeoPrepareApiCallCommand extends Command
{
    const NAME = 'vimeet:api:vianeo-export-data';

    /** @var VianeoPrepareApiCallHandler */
    private $vianeoPrepareApiCallHandler;

    /**
     * @param VianeoPrepareApiCallHandler $vianeoPrepareApiCallHandler
     */
    public function __construct(VianeoPrepareApiCallHandler $vianeoPrepareApiCallHandler)
    {
        parent::__construct(self::NAME);

        $this->vianeoPrepareApiCallHandler = $vianeoPrepareApiCallHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Send to the Vianeo API the data of sheet registered with the VIANEO_REGISTRATION tag to true')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->vianeoPrepareApiCallHandler->handle(new VianeoPrepareApiCall());
    }
}
