<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Model;

use Proximum\Vimeet\Application\Command\Package\Exception\WrongTypeException;
use Proximum\Vimeet\Domain\Model\Product;

class ParticipantAndPlanning
{
    /** @var array */
    public $labels;

    /** @var bool */
    public $enabled;

    /** @var Product[] */
    public $participants;

    /** @var int|null */
    public $maxParticipant;

    /** @var Product|null */
    public $planning;

    /** @var bool */
    public $planningSelectable = true;

    /**
     * @param array     $labels
     * @param bool      $enabled
     * @param int|null  $maxParticipant
     * @param Product[] $participants
     * @param Product   $planning
     * @param bool      $planningSelectable
     *
     * @throws WrongTypeException
     */
    public function __construct(
        array $labels,
        $enabled,
        $maxParticipant,
        array $participants = [],
        Product $planning = null,
        $planningSelectable
    ) {
        foreach ($participants as $participant) {
            if (null !== $participant && !$participant->isParticipant()) {
                throw new WrongTypeException($participant, Product::TYPE_PARTICIPANT);
            }
        }

        if (null !== $planning && !$planning->isPlanning()) {
            throw new WrongTypeException($planning, Product::TYPE_PLANNING);
        }

        $this->labels = $labels;
        $this->enabled = $enabled;
        $this->maxParticipant = $maxParticipant;
        $this->participants = $participants;
        $this->planning = $planning;
        $this->planningSelectable = $planningSelectable;
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
