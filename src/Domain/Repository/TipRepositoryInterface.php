<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Model\Type;

interface TipRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return Tip|null
     */
    public function getById($id);

    /**
     * @param int $page
     * @param int $limit
     *
     * @return PaginatedResult
     */
    public function paginate($page, $limit = 20);

    /**
     * @param Tip $tip
     */
    public function add(Tip $tip);

    /**
     * @param Tip $tip
     */
    public function set(Tip $tip);

    /**
     * @param TipTranslation $translation
     */
    public function removeTranslation(TipTranslation $translation);

    /**
     * @param Tip $tip
     */
    public function removeTip(Tip $tip);

    /**
     * @param Event  $event
     * @param Type   $type
     * @param string $context
     * @param string $locale
     *
     * @return TipTranslationView[]
     */
    public function getByContextAndEventAndType(Event $event, Type $type, $context, $locale);

    /**
     * @param Event $event
     * @param Tip   $tip
     *
     * @return Tip|null
     */
    public function getByEventAndTip(Event $event, Tip $tip);

    /**
     * @param Event $event
     * @param int   $page
     * @param int   $limit
     *
     * @return PaginatedResult
     */
    public function paginateByEvent(Event $event, $page, $limit);

    /**
     * @return Tip[]
     */
    public function getAll();

    /**
     * @param Tip   $tip
     * @param Event $event
     *
     * @return bool
     */
    public function isTipAffectedToEvent(Tip $tip, Event $event);

    /**
     * @param Event $event
     *
     * @return Tip[]
     */
    public function getByEvent(Event $event): array;

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return bool
     */
    public function isConfirmationPhoneEnabled(Event $event, Type $type): bool;

    /**
     * @return Tip[]
     */
    public function getGlobals(): array;

    /**
     * @deprecated This methods is used only for the migration of the old globals tips
     *
     * @return Tip[]
     */
    public function getTipWithoutEventWithType(): array;
}
