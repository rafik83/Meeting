<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PurchasingFunnel\Model;

use Proximum\Vimeet\Domain\Model\Product;

class ParticipantAndPlanning
{
    /**
     * @var array
     */
    public $labels;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var Product
     */
    public $participant;

    /**
     * @var Product
     */
    public $planning;

    /**
     * ParticipantAndPlanning constructor.
     *
     * @param array   $labels
     * @param bool    $enabled
     * @param Product $participant
     * @param Product $planning
     */
    public function __construct(array $labels, $enabled, Product $participant = null, Product $planning = null)
    {
        if ($participant && !$participant->isParticipant()) {
            throw new \RuntimeException();
        }

        if ($planning && !$planning->isPlanning()) {
            throw new \RuntimeException();
        }

        $this->labels      = $labels;
        $this->enabled     = $enabled;
        $this->participant = $participant;
        $this->planning    = $planning;
    }

    /**
     * @param string     $locale
     * @param mixed|null $default
     *
     * @return string|null
     */
    public function getLabel($locale, $default = null)
    {
        return isset($this->labels[$locale]) ? $this->labels[$locale] : $default;
    }
}
