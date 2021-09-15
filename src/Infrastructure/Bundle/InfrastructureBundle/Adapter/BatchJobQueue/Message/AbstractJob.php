<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message;

use DateTimeInterface;
use Symfony\Component\Lock\Key;

class AbstractJob
{
    private string $command;
    private array $args;
    private ?Key $lockKey = null;
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

    public function getLockKey(): Key
    {
        if (null !== $this->lockKey) {
            return $this->lockKey;
        }

        $this->lockKey = new Key($this->command.';'.implode(';', $this->args));

        return $this->lockKey;
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
