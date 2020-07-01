<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;

/**
 * "Règle".
 */
class Rule
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Type
     */
    private $seerType;

    /**
     * @var Category
     */
    private $seerCategory;

    /**
     * @var Type
     */
    private $seeableType;

    /**
     * @var Category
     */
    private $seeableCategory;

    /**
     * @var array
     */
    private $what;

    /**
     * @var int
     */
    private $priority;

    /** @var int|null */
    private $phoneAccessMinEvaluation;

    /** @var int|null */
    private $emailAccessMinEvaluation;

    /**
     * Rule constructor.
     *
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     * @param array        $what
     * @param int          $priority
     */
    public function __construct(Event $event, WhoInterface $seer, WhoInterface $seeable, array $what, $priority = 0)
    {
        $this->event    = $event;
        $this->what     = $what;
        $this->priority = $priority;

        if ($seer instanceof Type) {
            $this->seerType = $seer;
        } elseif ($seer instanceof Category) {
            $this->seerCategory = $seer;
        } else {
            throw new \InvalidArgumentException(sprintf('Do not know how to handle %s', get_class($seer)));
        }

        if ($seeable instanceof Type) {
            $this->seeableType = $seeable;
        } elseif ($seeable instanceof Category) {
            $this->seeableCategory = $seeable;
        } else {
            throw new \InvalidArgumentException(sprintf('Do not know how to handle %s', get_class($seeable)));
        }
    }

    public static function createDefault(Event $event, WhoInterface $seer, WhoInterface $seeable, $priority = 0): Rule
    {
        return new self($event, $seer, $seeable, Tag::getSeeableTags(), $priority);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get seerType.
     *
     * @return Type
     */
    public function getSeerType()
    {
        return $this->seerType;
    }

    /**
     * Get seerCategory.
     *
     * @return Category
     */
    public function getSeerCategory()
    {
        return $this->seerCategory;
    }

    /**
     * Get seeableType.
     *
     * @return Type
     */
    public function getSeeableType()
    {
        return $this->seeableType;
    }

    /**
     * Get seeableCategory.
     *
     * @return Category
     */
    public function getSeeableCategory()
    {
        return $this->seeableCategory;
    }

    /**
     * Get what.
     *
     * @return array
     */
    public function getWhat()
    {
        return $this->what;
    }

    /**
     * Set what.
     *
     * @param array $what
     *
     * @return Rule
     */
    public function setWhat(array $what)
    {
        $this->what = $what;

        return $this;
    }

    /**
     * @return WhoInterface
     */
    public function getSeer()
    {
        return $this->seerCategory ?: $this->seerType;
    }

    /**
     * @return WhoInterface
     */
    public function getSeeable()
    {
        return $this->seeableCategory ?: $this->seeableType;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    public function getPhoneAccessMinEvaluation(): ?int
    {
        return $this->phoneAccessMinEvaluation;
    }

    public function getEmailAccessMinEvaluation(): ?int
    {
        return $this->emailAccessMinEvaluation;
    }

    /**
     * @param array $what
     * @param int $priority
     * @param int|null $phoneAccessMinEvaluation
     * @param int|null $emailAccessMinEvaluation
     */
    public function update(array $what, $priority, ?int $phoneAccessMinEvaluation, ?int $emailAccessMinEvaluation)
    {
        $this->what     = $what;
        $this->priority = $priority;
        $this->phoneAccessMinEvaluation = $phoneAccessMinEvaluation;
        $this->emailAccessMinEvaluation = $emailAccessMinEvaluation;
    }
}
