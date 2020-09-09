<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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

    /**
     * @Given there is a rule between this type and :otherTypeName
     */
    public function createWith2Types($otherTypeName)
    {
        $event = $this->ruleContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $type = $this->ruleContextProxy->getStorage()->get('type');

        if (null === $type) {
            throw new \InvalidArgumentException('Missing Type');
        }

        $this->ruleContextProxy->getRuleManager()->createWith2Types($type, $otherTypeName, $event);
    }
}
