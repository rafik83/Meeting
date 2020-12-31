<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

/**
 * Traçabilité
 */
class Trace
{
    public const ACCEPT                         = 'accept';
    public const VALIDATE                       = 'validate';
    public const CREATE                         = 'create';
    public const UPDATE                         = 'update';
    public const ENABLE                         = 'enable';
    public const DISABLE                        = 'disable';
    public const ENABLE_CATALOG                 = 'enable_catalog';
    public const DISABLE_CATALOG                = 'disable_catalog';
    public const CHANGED_TYPE                   = 'changed_type';
    public const VALIDATION_DRAFT               = 'validation_draft';
    public const VALIDATION_VALIDATE            = 'validation_validate';
    public const PARTICIPANT_IMPORTED           = 'participant_imported';
    public const SHEET_CREATED_BY_GROUP_MANAGER = 'sheet_created_by_group_manager';
    public const PENDING                        = 'pending';
    public const SET_COMMERCIAL_STATUS          = 'set_commercial_status';
    public const ORDERS_CANCELLED               = 'orders_cancelled';
    public const SHEET_OWNER_CHANGED            = 'sheet_owner_changed';

    const ACTIONS_REQUIRED_TRANSLATION = [
        self::SET_COMMERCIAL_STATUS,
    ];

    /** @var int */
    private $id;

    /** @var string */
    private $action;

    /** @var User|null */
    private $user;

    /** @var Admin|null */
    private $admin;

    /** @var DateTimeInterface */
    private $date;

    /** @var string */
    private $comment;

    /** @var string */
    private $objectType;

    /** @var int */
    private $objectId;

    /**
     * @param TraceableInterface $traceable
     * @param string             $action
     * @param DateTimeInterface  $date
     * @param string             $comment
     * @param null|AbstractUser  $abstractUser
     */
    public function __construct(
        TraceableInterface $traceable,
        $action,
        DateTimeInterface $date,
        $comment,
        AbstractUser $abstractUser = null
    ) {
        $this->objectType = $traceable->getTraceableName();
        $this->objectId   = $traceable->getId();
        $this->action     = $action;
        $this->date       = $date;
        $this->comment    = $comment;

        if ($abstractUser instanceof User) {
            $this->user = $abstractUser;
        } elseif ($abstractUser instanceof Admin) {
            $this->admin = $abstractUser;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|Admin
     */
    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        if (null !== $this->user) {
            return $this->user->getEmail();
        } elseif (null !== $this->admin) {
            return sprintf('%s %s', $this->admin->getFirstname(), $this->admin->getLastname());
        }

        return '';
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param Trace[]            $traces
     * @param TraceableInterface $traceable
     *
     * @return null|Trace
     */
    public static function find(array &$traces, TraceableInterface $traceable)
    {
        foreach ($traces as $trace) {
            if ($trace->getObjectType() === $traceable->getTraceableName()
                && $trace->getObjectId() === $traceable->getId()
            ) {
                return $trace;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasToBeTranslated(): bool
    {
        return in_array($this->action, self::ACTIONS_REQUIRED_TRANSLATION, true);
    }
}
