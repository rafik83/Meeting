<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;

interface RuleRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return Rule[]
     */
    public function getByEvent(Event $event);

    /**
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     *
     * @return Rule|null
     */
    public function getByEventSeerAndSeeable(Event $event, WhoInterface $seer, WhoInterface $seeable);

    /**
     * @param Type $seer
     * @param Type $seeable
     *
     * @return Rule[]
     */
    public function getBySeerTypeAndSeeableType(Type $seer, Type $seeable);

    /**
     * @param Rule $rule
     *
     * @return Rule
     */
    public function add(Rule $rule);

    /**
     * @param Rule $rule
     */
    public function remove(Rule $rule);

    /**
     * @param Rule $rule
     *
     * @return Rule
     */
    public function update(Rule $rule);
}
