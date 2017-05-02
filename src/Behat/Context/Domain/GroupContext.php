<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\GroupContextProxyInterface;

class GroupContext implements Context
{
    /** @var GroupContextProxyInterface */
    private $groupContextProxy;

    /**
     * GroupContext constructor.
     *
     * @param GroupContextProxyInterface $groupContextProxy
     */
    public function __construct(GroupContextProxyInterface $groupContextProxy)
    {
        $this->groupContextProxy = $groupContextProxy;
    }

    /**
     * @Given /^there is a group "(?P<groupTitle>[^"]+)" managed by this user$/
     *
     * @param string $groupTitle
     */
    public function createGroup($groupTitle)
    {
        $event = $this->groupContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $user = $this->groupContextProxy->getStorage()->get('user');

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $group = $this->groupContextProxy->getGroupManager()->create($event, $user, null, $groupTitle);
        $this->groupContextProxy->getStorage()->set('group', $group);
    }
}
