<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class PlannerJob
{
    /** PlannerJob is pending */
    const STATUS_PENDING = 'pending';

    /** Error before sending file to planner or error received by the Jenkins build (see $errorMessage) */
    const STATUS_ERROR = 'error';

    /**
     * File from Vimeet is generated and build call has been made to Jenkins
     * or Build is started after been queued in Jenkins
     */
    const STATUS_STARTED = 'build_started';

    /** Build is queued in Jenkins */
    const STATUS_QUEUED = 'build_queued';

    /** Build is aborted in Jenkins */
    const STATUS_ABORTED = 'build_aborted';

    /** Planner finished its task, the file *_solved.xml is created */
    const STATUS_SUCCESS = 'build_success';

    /** File is imported, meetings are created */
    const STATUS_COMPLETED = 'completed';

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
    public function isPending(): bool
    {
        return self::STATUS_PENDING === $this->status;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return self::STATUS_STARTED === $this->status;
    }

    /**
     * @return bool
     */
    public function isQueued(): bool
    {
        return self::STATUS_QUEUED === $this->status;
    }

    /**
     * @return bool
     */
    public function isAborted(): bool
    {
        return self::STATUS_ABORTED === $this->status;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return self::STATUS_SUCCESS === $this->status;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return self::STATUS_COMPLETED === $this->status;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return self::STATUS_ERROR === $this->status;
    }

    public function setPending()
    {
        $this->status = self::STATUS_PENDING;
    }

    public function setStarted()
    {
        $this->status = self::STATUS_STARTED;
    }

    public function setQueued()
    {
        $this->status = self::STATUS_QUEUED;
    }

    public function setAborted()
    {
        $this->status = self::STATUS_ABORTED;
    }

    public function setSuccess()
    {
        $this->status = self::STATUS_SUCCESS;
    }

    public function setCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
    }
}
