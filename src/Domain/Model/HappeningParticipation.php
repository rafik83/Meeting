<?php

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
    public function __construct(Happening $happening, User $user, $disabled = false)
    {
        $this->happening = $happening;
        $this->user      = $user;
        $this->disabled  = $disabled;
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

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
