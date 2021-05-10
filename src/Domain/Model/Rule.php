<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;

/**
 * "Règle".
 */
class Rule
{
    private int $id;
    private Event $event;
    private ?Type $seerType = null;
    private ?Category $seerCategory = null;
    private ?Type $seeableType = null;
    private ?Category $seeableCategory = null;
    private array $what;
    private int $priority;
    private ?int $phoneAccessMinEvaluation = null;
    private ?int $emailAccessMinEvaluation = null;
    private ?int $sendEmailMinEvaluation = null;
    private bool $requestAutomaticallyTransformedIntoMeeting = false;
    private bool $disableMeetingRequest = false;

    public function __construct(
        Event $event,
        WhoInterface $seer,
        WhoInterface $seeable,
        array $what,
        int $priority = 0,
        bool $requestAutomaticallyTransformedIntoMeeting = false
    ) {
        $this->event    = $event;
        $this->what     = $what;
        $this->priority = $priority;
        $this->requestAutomaticallyTransformedIntoMeeting = $requestAutomaticallyTransformedIntoMeeting;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getSeerType(): ?Type
    {
        return $this->seerType;
    }

    public function getSeerCategory(): ?Category
    {
        return $this->seerCategory;
    }

    public function getSeeableType(): ?Type
    {
        return $this->seeableType;
    }

    public function getSeeableCategory(): ?Category
    {
        return $this->seeableCategory;
    }

    public function getWhat(): array
    {
        return $this->what;
    }

    public function setWhat(array $what): Rule
    {
        $this->what = $what;

        return $this;
    }

    public function getSeer(): WhoInterface
    {
        return $this->seerCategory ?: $this->seerType;
    }

    public function getSeeable(): WhoInterface
    {
        return $this->seeableCategory ?: $this->seeableType;
    }

    public function getPriority(): int
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

    public function getSendEmailMinEvaluation(): ?int
    {
        return $this->sendEmailMinEvaluation;
    }

    public function getRequestAutomaticallyTransformedIntoMeeting(): bool
    {
        return $this->requestAutomaticallyTransformedIntoMeeting;
    }

    public function update(
        array $what,
        $priority,
        ?int $phoneAccessMinEvaluation,
        ?int $emailAccessMinEvaluation,
        ?int $sendEmailMinEvaluation,
        bool $requestAutomaticallyTransformedIntoMeeting
    ) {
        $this->what     = $what;
        $this->priority = $priority;
        $this->phoneAccessMinEvaluation = $phoneAccessMinEvaluation;
        $this->emailAccessMinEvaluation = $emailAccessMinEvaluation;
        $this->sendEmailMinEvaluation = $sendEmailMinEvaluation;
        $this->requestAutomaticallyTransformedIntoMeeting = $requestAutomaticallyTransformedIntoMeeting;
    }
}
