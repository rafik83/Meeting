<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class HappeningParticipation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Happening
     */
    private $happening;

    /**
     * @var bool
     */
    private $disabled = false;

    /**
     * @var bool can user see this participation (eg in agenda)
     */
    private $visible = false;

    /**
     * @var User
     */
    private $user;

    /**
     * HappeningParticipation constructor.
     *
     * @param Happening $happening
     * @param User      $user
     * @param bool      $disabled
     */
    public function __construct(Happening $happening, User $user, $disabled = false, $visible = true)
    {
        $this->happening = $happening;
        $this->user      = $user;
        $this->disabled  = $disabled;
        $this->visible  = $visible;
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
     * Get happening.
     *
     * @return Happening
     */
    public function getHappening()
    {
        return $this->happening;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     *
     * @return HappeningParticipation
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
