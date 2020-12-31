<?php

namespace Proximum\Vimeet\Domain\Model;

class UserEvent
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Type
     */
    private $type;

    /**
     * UserEvent constructor.
     *
     * @param User      $user
     * @param Event     $event
     * @param Type|null $type
     */
    public function __construct(User $user, Event $event, Type $type = null)
    {
        $this->user  = $user;
        $this->event = $event;
        $this->type  = $type;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return UserEvent
     */
    public function setType(Type $type)
    {
        $this->type = $type;

        return $this;
    }
}
