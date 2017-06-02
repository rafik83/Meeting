<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView as EventTipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Model\Type;

interface TipRepositoryInterface
{
    /**
     * @param int   $id
     * @param Event $event
     *
     * @return Tip|null
     */
    public function getByTipTranslationId($id, Event $event);

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
     * @param Tip $tip
     */
    public function setTypes(Tip $tip);

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
     * @param string $locale
     *
     * @return TipTranslationView[]
     */
    public function getTipTranslationViewByLocale($locale);

    /**
     * @param string $locale
     *
     * @return EventTipTranslationView[]
     */
    public function getTipTranslationViewByLocaleForEvent($locale);
}
