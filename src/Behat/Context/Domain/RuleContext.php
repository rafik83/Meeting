<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\RuleContextProxyInterface;

class RuleContext implements Context
{
    /**
     * @var RuleContextProxyInterface
     */
    private $ruleContextProxy;

    /**
     * RuleContext constructor.
     *
     * @param RuleContextProxyInterface $ruleContextProxy
     */
    public function __construct(RuleContextProxyInterface $ruleContextProxy)
    {
        $this->ruleContextProxy = $ruleContextProxy;
    }

    /**
     * @Given /^there is a rule for this type and this event$/
     */
    public function create()
    {
        $event = $this->ruleContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $type = $this->ruleContextProxy->getStorage()->get('type');

        if (null === $type) {
            throw new \InvalidArgumentException('Missing Type');
        }

        $this->ruleContextProxy->getRuleManager()->create($type, $event);
    }
}
