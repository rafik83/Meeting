<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class JobHandler implements MessageHandlerInterface
{
    private KernelInterface $kernel;
    private LoggerInterface $logger;
    private bool $isDebugMode;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger, bool $isDebugMode)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->isDebugMode = $isDebugMode;

    }

    function __invoke(Job $job)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(!$this->isDebugMode);

        $input = new ArrayInput(array_merge(
            ['command' => $job->getCommand()],
            $job->getArgs(),
        ));

        $returnCode = $application->run($input);

        if ($returnCode !== 0) {
            $this->logger->error(sprintf('Error %d while running command %s', $returnCode, (string) $input));
        } else {
            $this->logger->info(sprintf('Command %s ran successfully', (string) $input));
        }
    }
}
