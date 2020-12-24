<?php

namespace Proximum\Vimeet\Domain\Model\Unavailability;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Exception\Unavailability\InvalidTimeSlotException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class Mass implements TimeRangeInterface
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
     * @var \DateTimeInterface
     */
    private $begin;

    /**
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * @var bool
     */
    private $blocking = true;

    /**
     * @var Category
     */
    private $category;

    /**
     * Admin name of the mass unavailability
     *
     * @var string
     */
    private $name;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var bool
     */
    private $dispatch = false;

    /**
     * @var ArrayCollection
     */
    private $timeSlots;

    /** @var ArrayCollection of Type */
    private $types;

    /**
     * @param Event              $event
     * @param Category           $category
     * @param string             $name
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $blocking
     * @param bool               $dispatch
     * @param array              $timeSlots
     * @param Type[]             $types
     */
    public function __construct(
        Event $event,
        Category $category,
        $name,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $blocking,
        $dispatch = false,
        array $timeSlots = [],
        array $types = []
    ) {
        if ($begin >= $end) {
            throw new InvalidTimeSlotException('Begin date must be lesser than end date.');
        }

        $this->translations = new ArrayCollection();
        $this->event        = $event;
        $this->category     = $category;
        $this->name         = $name;
        $this->begin        = $begin;
        $this->end          = $end;
        $this->blocking     = $blocking;
        $this->dispatch     = $dispatch;
        $this->timeSlots    = new ArrayCollection();
        $this->types        = new ArrayCollection($types);

        $this->setTimeSlots($timeSlots);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return bool
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return MassTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getTitle() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getDescription()
            : '';
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function update(
        Category $category,
        string $name,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        bool $blocking,
        bool $dispatch = false,
        array $timeSlots = [],
        array $types = []
    ) {
        $this->category = $category;
        $this->name     = $name;
        $this->begin    = $begin;
        $this->end      = $end;
        $this->blocking = $blocking;
        $this->dispatch = $dispatch;
        $this->types    = new ArrayCollection($types);

        $this->setTimeSlots($timeSlots);
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     */
    public function updateTranslation($locale, $title, $description)
    {
        $this->createTranslation($locale, $title, $description);
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     */
    public function createTranslation($locale, $title, $description)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($title, $description);
        } else {
            $this->translations->set($locale, new MassTranslation($this, $locale, $title, $description));
        }
    }

    /**
     * Get dispatch
     *
     * @return bool
     */
    public function isDispatch()
    {
        return $this->dispatch;
    }

    /**
     * Get timeSlots
     *
     * @return MassTimeSlot[]
     */
    public function getTimeSlots()
    {
        return $this->timeSlots->toArray();
    }

    /**
     * @param array $timeSlots
     *
     * @return $this
     */
    private function setTimeSlots(array $timeSlots)
    {
        // Update and add
        foreach ($timeSlots as $key => $value) {
            if ($value['from'] < $this->begin || $this->end < $value['to']) {
                throw new InvalidTimeSlotException('Time slots can\'t exceed mass unavailabilty date range.');
            }

            if ($this->timeSlots->containsKey($key)) {
                $this->timeSlots->get($key)->setFrom($value['from'])->setTo($value['to']);
            } else {
                $this->timeSlots->set($key, new MassTimeSlot($this, $value['from'], $value['to']));
            }
        }

        // Remove deleted
        foreach ($this->timeSlots as $key => $value) {
            if (!isset($timeSlots[$key])) {
                $this->timeSlots->removeElement($value);
            }
        }

        return $this;
    }

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function setDates(\DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    public function hasType(Type $type): bool
    {
        foreach ($this->getTypes() as $massType) {
            if ($type->getId() === $massType->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Type[] $types
     */
    public function hasAtLeastOneType(array $types): bool
    {
        foreach ($types as $type) {
            if ($this->hasType($type)) {
                return true;
            }
        }

        return false;
    }

    public function countTypes(): int
    {
        return $this->types->count();
    }

    public function isBlockingAndNotDispatch(): bool
    {
        return $this->isBlocking() && !$this->isDispatch();
    }
}
