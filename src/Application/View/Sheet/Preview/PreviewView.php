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

class PreviewView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $strong = false;

    /**
     * @var array|CardView[]
     */
    public $cardViews;

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->type === 'image';
    }

    /**
     * @return bool
     */
    public function isParticipant()
    {
        return $this->type === 'participant';
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
     */
    public function __construct($id, $content, $type, array $cardViews = [])
    {
        $this->id        = $id;
        $this->content   = $content;
        $this->type      = $type;
        $this->cardViews = $cardViews;
    }
}
