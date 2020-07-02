<?php

namespace Proximum\Vimeet\Domain\Model\Happening\Webinar;

use Proximum\Vimeet\Domain\Happening\Webinar\RecordStatus;
use Proximum\Vimeet\Domain\Model\Happening;

class RecordArchive
{
    /** @var int|null */
    private $id;

    /** @var Happening */
    private $happening;

    /** @var string */
    private $archiveId;

    /** @var string */
    private $status;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var string|null */
    private $path;

    public function __construct(
        Happening $happening,
        string $archiveId,
        \DateTimeInterface $createdAt
    ) {
        $this->happening = $happening;
        $this->archiveId = $archiveId;
        $this->status = RecordStatus::STARTED;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }

    public function getArchiveId(): string
    {
        return $this->archiveId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
