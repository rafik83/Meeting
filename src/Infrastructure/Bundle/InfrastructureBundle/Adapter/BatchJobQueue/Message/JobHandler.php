<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class JobHandler implements MessageHandlerInterface
{
    private KernelInterface $kernel;
    private bool $isDebugMode;
    private LockFactory $jobLockFactory;
    private ?LoggerInterface $logger;

    public function __construct(
        KernelInterface $kernel,
        bool $isDebugMode,
        LockFactory $jobLockFactory,
        LoggerInterface $logger = null
    ) {
        $this->kernel = $kernel;
        $this->isDebugMode = $isDebugMode;
        $this->jobLockFactory = $jobLockFactory;
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

        $lock = $this->jobLockFactory->createLock($job->getLockKey());
        $lock->release();
    }
}
