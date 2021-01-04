<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;

class CampaignView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var \DateTimeInterface */
    public $sentAt;

    /** @var bool */
    public $hasMessage;

    /** @var bool */
    public $hasRecipients;

    /** @var bool */
    public $sent;

    /** @var bool */
    public $processed;

    /**
     * @param int                     $id
     * @param string                  $title
     * @param \DateTimeInterface      $createdAt
     * @param \DateTimeInterface|null $sentAt
     * @param \DateTimeInterface|null $processedAt
     * @param bool                    $hasMessage
     * @param bool                    $hasRecipients
     */
    public function __construct(
        $id,
        $title,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $sentAt = null,
        \DateTimeInterface $processedAt = null,
        $hasMessage = false,
        $hasRecipients = false
    ) {
        $this->id            = $id;
        $this->title         = $title;
        $this->createdAt     = $createdAt;
        $this->sentAt        = $sentAt;
        $this->hasMessage    = $hasMessage;
        $this->hasRecipients = $hasRecipients;
        $this->sent          = (bool) $sentAt;
        $this->processed     = (bool) $processedAt;
    }

    /**
     * @param Campaign $campaign
     *
     * @return CampaignView
     */
    public static function createFromCampaign(Campaign $campaign)
    {
        return new self(
            $campaign->getId(),
            $campaign->getTitle(),
            $campaign->getCreatedAt(),
            $campaign->getSentAt(),
            $campaign->getProcessedAt(),
            (bool) $campaign->getMessage(),
            (bool) $campaign->getRecipients()
        );
    }
}
