<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\CrossProcessLockFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class JobHandler implements MessageHandlerInterface
{
    private KernelInterface $kernel;
    private CrossProcessLockFactory $jobLockFactory;
    private bool $isDebugMode;
    private ?LoggerInterface $logger;

    public function __construct(
        KernelInterface $kernel,
        CrossProcessLockFactory $jobLockFactory,
        bool $isDebugMode,
        LoggerInterface $logger = null
    ) {
        $this->kernel = $kernel;
        $this->jobLockFactory = $jobLockFactory;
        $this->isDebugMode = $isDebugMode;
        $this->logger = $logger;
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

        $lock = $lock = $this->jobLockFactory->createLockForRelease($job);
        $lock->release();
    }
}
