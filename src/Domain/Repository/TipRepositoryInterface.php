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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;

interface TipRepositoryInterface
{
    /**
     * @param $id
     *
     * @return Tip|null
     */
    public function getByTipTranslationId($id);

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
     * @param $context
     * @param $locale
     *
     * @return TipTranslationView[]
     */
    public function getByContext($context, $locale);

    /**
     * @param Event $event
     * @param int   $page
     * @param int   $limit
     *
     * @return PaginatedResult[]
     */
    public function paginateByEvent(Event $event, $page, $limit);

    /**
     * @param string $locale
     *
     * @return TipTranslationView[]
     */
    public function findAll($locale);
}
