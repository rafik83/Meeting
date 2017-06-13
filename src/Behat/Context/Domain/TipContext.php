<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\TipContextProxyInterface;

class TipContext implements Context
{
    /** @var TipContextProxyInterface */
    private $tipContextProxy;

    /**
     * @param TipContextProxyInterface $tipContextProxy
     */
    public function __construct(TipContextProxyInterface $tipContextProxy)
    {
        $this->tipContextProxy = $tipContextProxy;
    }

    /**
     * @Given /^the tip "(?P<tipTitle>[^"]+)" is created$/
     *
     * @param string $title
     */
    public function createTip($title)
    {
        $tip = $this->tipContextProxy->getTipManager()->create($title);
        $this->tipContextProxy->getStorage()->set('tip', $tip);
    }

    /**
     * @Given /^the tip "(?P<tipTitle>[^"]+)" is created for the event "(?P<eventTitle>[^"]+)"$/
     *
     * @param string $title
     * @param string $eventTitle
     */
    public function createTipForEvent($title, $eventTitle)
    {
        $tip = $this->tipContextProxy->getTipManager()->createForEvent($title, $eventTitle);
        $this->tipContextProxy->getStorage()->set('tip', $tip);
    }
}
