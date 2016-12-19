<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param int                $id
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     */
    public function __construct($id, $title, \DateTimeInterface $createdAt)
    {
        $this->id        = $id;
        $this->title     = $title;
        $this->createdAt = $createdAt;
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
            $campaign->getCreatedAt()
        );
    }
}
