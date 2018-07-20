<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Preview;

use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewData;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Template\AbstractChild;

class PreviewView
{
    /** @var string */
    public $id;

    /** @var string */
    public $content;

    /** @var string */
    public $type;

    /** @var bool */
    public $strong = false;

    /** @var CardView[] */
    public $cardViews;

    /** @var bool */
    public $link = false;

    /** @var TagView[] */
    public $tagViews;

    /** @var string */
    public $populatedFromTag;

    /** @var bool */
    public $canDisplayImage;

    public function __construct(
        string $id,
        string $content,
        string $type,
        array $cardViews = [],
        bool $link = false,
        bool $canDisplayImage = true
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->type = $type;
        $this->cardViews = $cardViews;
        $this->tagViews = [];
        $this->link = $link;
        $this->canDisplayImage = $canDisplayImage;
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return AbstractChild::TEMPLATE_OBJECT_TYPE_IMAGE === $this->type;
    }

    /**
     * @return bool
     */
    public function isParticipant()
    {
        return AbstractChild::TEMPLATE_OBJECT_TYPE_PARTICIPANT === $this->type;
    }

    /**
     * @return bool
     */
    public function isTag()
    {
        return AbstractChild::TEMPLATE_OBJECT_TYPE_TAG === $this->type;
    }

    /**
     * @return bool
     */
    public function isParticipantsPosition()
    {
        return CustomPreviewData::PARTICIPANTS_POSITION === $this->type;
    }

    /**
     * @return bool
     */
    public function isStrong()
    {
        return false !== $this->strong;
    }

    /**
     * @return bool
     */
    public function isPopulatedFromTagSheetOrganization(): bool
    {
        return Tag::SHEET_ORGANIZATION === $this->populatedFromTag;
    }

    /**
     * @param TagView $tagView
     */
    public function addTagView(TagView $tagView)
    {
        $this->tagViews[] = $tagView;
    }
}
