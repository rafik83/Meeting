<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class PlannerJob
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';

    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var Admin */
    private $admin;

    /** @var string */
    private $solutionType;

    /** @var bool */
    private $lockMeetingRequest;

    /** @var string */
    private $status;

    /** @var null|File */
    private $file;

    /** @var string */
    private $errorMessage;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * @param Event              $event
     * @param Admin              $admin
     * @param string             $solutionType
     * @param bool               $lockMeetingRequest
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        Admin $admin,
        string $solutionType,
        bool $lockMeetingRequest,
        \DateTimeInterface $createdAt
    ) {
        if (!in_array($solutionType, ExportSolutionType::getExportSolutionTypes(), true)) {
            throw new \InvalidArgumentException('solutionType must be one of ExportSolutionType');
        }

        $this->event = $event;
        $this->admin = $admin;
        $this->solutionType = $solutionType;
        $this->lockMeetingRequest = $lockMeetingRequest;
        $this->createdAt = $createdAt;

        $this->status = self::STATUS_PENDING;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return Admin
     */
    public function getAdmin(): Admin
    {
        return $this->admin;
    }

    /**
     * @return string
     */
    public function getSolutionType(): string
    {
        return $this->solutionType;
    }

    /**
     * @return bool
     */
    public function isLockMeetingRequest(): bool
    {
        return $this->lockMeetingRequest;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return null|File
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param File $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    /**
     * @return null|string
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setError(string $errorMessage): void
    {
        $this->status = self::STATUS_ERROR;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return self::STATUS_ERROR == $this->status;
    }
}
