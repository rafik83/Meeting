<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;

interface TipRepositoryInterface
{
    /**
     * @param int $page
     * @param int $limit
     *
     * @return Tip[]
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
}
