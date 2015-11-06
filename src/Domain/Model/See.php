<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class See
{
    /**
     * @var Type
     */
    private $seerType;

    /**
     * @var Category
     */
    private $seerCategory;

    /**
     * @var Type
     */
    private $seenType;

    /**
     * @var Category
     */
    private $seenCategory;

    /**
     * @param WhoInterface $seer
     * @param WhoInterface $seen
     */
    public function __construct(WhoInterface $seer, WhoInterface $seen)
    {
        if ($seer instanceof Type) {
            $this->seerType = $seer;
        } elseif ($seer instanceof Category) {
            $this->seerCategory = $seer;
        } else {
            throw new \InvalidArgumentException(sprintf('Unknow to handle %s', get_class($seer)));
        }
        
        if ($seen instanceof Type) {
            $this->seenType = $seen;
        } elseif ($seen instanceof Category) {
            $this->seenCategory = $seen;
        } else {
            throw new \InvalidArgumentException(sprintf('Unknow to handle %s', get_class($seen)));
        }
    }

    /**
     * Get seerType
     *
     * @return Type
     */
    public function getSeerType()
    {
        return $this->seerType;
    }

    /**
     * Get seerCategory
     *
     * @return Category
     */
    public function getSeerCategory()
    {
        return $this->seerCategory;
    }

    /**
     * Get seenType
     *
     * @return Type
     */
    public function getSeenType()
    {
        return $this->seenType;
    }

    /**
     * Get seenCategory
     *
     * @return Category
     */
    public function getSeenCategory()
    {
        return $this->seenCategory;
    }
}
