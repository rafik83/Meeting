<?php

namespace Proximum\Vimeet\Application\Query\Dashboard\View;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class DashboardRequestView
{
    /** @var int */
    private $fromTypeId;

    /** @var string */
    private $state;

    /** @var bool */
    private $planned;

    public function __construct(int $fromTypeId, string $state, ?int $meetingId)
    {
        $this->fromTypeId = $fromTypeId;
        $this->state = $state;
        $this->planned = null !== $meetingId;
    }

    public function getFromTypeId(): int
    {
        return $this->fromTypeId;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function isPlanned(): bool
    {
        return $this->planned;
    }

    public function isApproved(): bool
    {
        return Request::STATE_APPROVED === $this->state;
    }

    public function isRefused(): bool
    {
        return Request::STATE_REFUSED === $this->state;
    }

    public function isPending(): bool
    {
        return Request::STATE_SENT === $this->state;
    }
}
