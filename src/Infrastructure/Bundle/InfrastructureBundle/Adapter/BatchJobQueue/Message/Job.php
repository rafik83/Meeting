<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message;

use DateTimeInterface;

class Job
{
    /** State if job is inserted, but not yet ready to be started. */
    const STATE_NEW = 'new';

    private string $command;
    private array $args;
    private ?string $lockKey = null;
    private int $maxExecutionTime = 300;
    private ?DateTimeInterface $executeAfter = null;

    public function __construct(string $command, array $args)
    {
        $this->command = $command;
        $this->args = $args;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getLockKey(): string
    {
        return $this->lockKey ?: $this->command.';'.implode(';', $this->args);
    }

    public function isDelayed(): bool
    {
        return null !== $this->executeAfter;
    }

    /**
     * Get delay in milliseconds
     */
    public function getDelay(): int
    {
        return ($this->executeAfter->getTimestamp() - time()) * 1000;
    }

    public function setExecuteAfter(DateTimeInterface $executeAfter): void
    {
        $this->executeAfter = $executeAfter;
    }

    public function getMaxExecutionTime(): int
    {
        return $this->maxExecutionTime;
    }

    /**
     * Change default max execution time (value is in seconds)
     */
    public function setMaxExecutionTime(int $maxExecutionTime): void
    {
        $this->maxExecutionTime = $maxExecutionTime;
    }
}
