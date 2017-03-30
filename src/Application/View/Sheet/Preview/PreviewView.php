<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Preview;

use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Template\TemplateType;

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

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->type === TemplateType::TEMPLATE_OBJECT_TYPE_IMAGE;
    }

    /**
     * @return bool
     */
    public function isParticipant()
    {
        return $this->type === TemplateType::TEMPLATE_OBJECT_TYPE_PARTICIPANT;
    }

    /**
     * @return bool
     */
    public function isTag()
    {
        return $this->type === TemplateType::TEMPLATE_OBJECT_TYPE_TAG;
    }

    /**
     * @return bool
     */
    public function isStrong()
    {
        return $this->strong !== false;
    }

    /**
     * @param string     $id
     * @param string     $content
     * @param string     $type
     * @param CardView[] $cardViews
     * @param TagView[]  $tagViews
     */
    public function __construct($id, $content, $type, array $cardViews = [], array $tagViews = [])
    {
        $this->id        = $id;
        $this->content   = $content;
        $this->type      = $type;
        $this->cardViews = $cardViews;
        $this->tagViews  = $tagViews;
    }
}
