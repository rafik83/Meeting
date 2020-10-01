<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Type;

interface HappeningRepositoryInterface
{
    public function add(Happening $happening): void;
    public function set(Happening $happening): void;

    public function getById(int $id): ?Happening;

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Happening[]
     */
    public function findListByEvent(Event $event, $locale);

    /**
     * @param array $happenings
     *
     * @return mixed
     */
    public function findIdsWithoutParticipation(array $happenings);

    /**
     * @param Event $event
     *
     * @return Happening[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event                   $event
     * @param Type                    $type
     * @param \DateTimeInterface      $day
     * @param Happening\Category|null $category
     *
     * @return Happening[]
     */
    public function findByEventAndTypeAndDayAndCategory(
        Event $event,
        Type $type,
        \DateTimeInterface $day,
        Happening\Category $category = null
    );

    /**
     * @param Speaker $speaker
     * @param string  $locale
     *
     * @return Happening[]
     */
    public function findBySpeaker(Speaker $speaker, $locale);

    /**
     * @param Event $event
     *
     * @return Happening[]
     */
    public function findHappeningParticipant(Event $event);

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return Happening[]
     */
    public function findWithProductsAndType(Event $event, Type $type): array;

    public function findWebinarBySessionId(string $sessionId): ?Happening;
}
