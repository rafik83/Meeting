<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class HappeningBroadcast
{
    /** @var int|null */
    private $id;

    /** @var Happening */
    private $happening;

    /** @var string */
    private $broadcastId;

    /** @var string|null */
    private $hlsUrl;

    /** @var bool */
    private $isStopped;

    /** @var \DateTimeInterface */
    private $createdAt;

    public function __construct(
        Happening $happening,
        string $broadcastId,
        bool $isStopped,
        \DateTimeInterface $createdAt,
        ?string $hlsUrl = null
    ) {
        $this->happening = $happening;
        $this->broadcastId = $broadcastId;
        $this->isStopped = $isStopped;
        $this->hlsUrl = $hlsUrl;
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

    public function getBroadcastId(): string
    {
        return $this->broadcastId;
    }

    public function getHlsUrl(): ?string
    {
        return $this->hlsUrl;
    }

    public function isStopped(): bool
    {
        return $this->isStopped;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
