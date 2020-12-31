<?php

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
     * This is used to explicitly set : "a participant = a planning" without buying a planning for each one
     *
     * @var bool
     */
    public $participantWithPlanning = false;

    /**
     * @param array     $labels
     * @param bool      $enabled
     * @param int|null  $maxParticipant
     * @param Product[] $participants
     * @param Product   $planning
     * @param bool      $planningSelectable
     * @param bool      $participantWithPlanning
     *
     * @throws WrongTypeException
     */
    public function __construct(
        array $labels,
        $enabled,
        $maxParticipant,
        array $participants = [],
        Product $planning = null,
        bool $planningSelectable,
        bool $participantWithPlanning
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
        $this->participantWithPlanning = $participantWithPlanning;
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
