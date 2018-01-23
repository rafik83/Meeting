<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
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

    /**
     * @param string     $id
     * @param string     $content
     * @param string     $type
     * @param CardView[] $cardViews
     * @param bool       $link
     */
    public function __construct($id, $content, $type, array $cardViews = [], bool $link = false)
    {
        $this->id        = $id;
        $this->content   = $content;
        $this->type      = $type;
        $this->cardViews = $cardViews;
        $this->tagViews  = [];
        $this->link      = $link;
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->type === AbstractChild::TEMPLATE_OBJECT_TYPE_IMAGE;
    }

    /**
     * @return bool
     */
    public function isParticipant()
    {
        return $this->type === AbstractChild::TEMPLATE_OBJECT_TYPE_PARTICIPANT;
    }

    /**
     * @return bool
     */
    public function isTag()
    {
        return $this->type === AbstractChild::TEMPLATE_OBJECT_TYPE_TAG;
    }

    /**
     * @return bool
     */
    public function isParticipantsPosition()
    {
        return $this->type === CustomPreviewData::PARTICIPANTS_POSITION;
    }

    /**
     * @return bool
     */
    public function isStrong()
    {
        return $this->strong !== false;
    }

    /**
     * @return bool
     */
    public function isPopulatedFromTagSheetOrganization(): bool
    {
        return $this->populatedFromTag === Tag::SHEET_ORGANIZATION;
    }

    /**
     * @param TagView $tagView
     */
    public function addTagView(TagView $tagView)
    {
        $this->tagViews[] = $tagView;
    }
}
