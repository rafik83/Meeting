<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\CrossProcessLockFactory;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\EntityManagerAdapter;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;

class RunJob
{
    private KernelInterface $kernel;
    private CrossProcessLockFactory $jobLockFactory;
    private bool $isDebugMode;
    private bool $resetAfterRun;
    private ?LoggerInterface $logger;
    private EntityManagerAdapter $entityManager;

    public function __construct(
        KernelInterface $kernel,
        CrossProcessLockFactory $jobLockFactory,
        bool $isDebugMode,
        bool $resetAfterRun,
        EntityManagerAdapter $entityManager,
        LoggerInterface $logger = null
    ) {
        $this->kernel = $kernel;
        $this->jobLockFactory = $jobLockFactory;
        $this->isDebugMode = $isDebugMode;
        $this->resetAfterRun = $resetAfterRun;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function run(AbstractJob $job)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(!$this->isDebugMode);

        $input = new ArrayInput(array_merge(
            ['command' => $job->getCommand()],
            $job->getArgs(),
        ));

        $returnCode = $application->run($input);

        if ($this->resetAfterRun) {
            $this->entityManager->clear();
        }

        if ($returnCode !== 0) {
            $this->logger->error(sprintf('Error %d while running command %s', $returnCode, (string) $input));
        } else {
            $this->logger->info(sprintf('Command %s ran successfully', (string) $input));
        }

        $lock = $this->jobLockFactory->createLockForRelease($job);
        $lock->release();
    }
}
