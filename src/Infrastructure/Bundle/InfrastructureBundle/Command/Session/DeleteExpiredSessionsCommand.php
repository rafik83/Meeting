<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Session;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DeleteExpiredSessionsCommand extends Command
{
    protected static $defaultName = 'vimeet:session:clean';

    /** @var \SessionHandlerInterface */
    private $sessionHandler;

    public function __construct(SessionInterface $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Delete expired sessions')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // We need to open SessionHandler
        $this->sessionHandler->open('', '');

        // Enable the garbage collector with a whatever $maxlifetime (0)
        $this->sessionHandler->gc(0);

        // Close the SessionHandler to handle garbage collector
        $this->sessionHandler->close();
    }
}
