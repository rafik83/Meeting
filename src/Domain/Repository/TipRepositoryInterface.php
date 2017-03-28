<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

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
     * @param $id
     *
     * @return null|Tip
     */
    public function getById($id);
}
