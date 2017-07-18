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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\TipFactory;

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

    /**
     * @Given /^a tip "(?P<tipTitle>[^"]+)" is enabled on confirmation phone context for this type$/
     *
     * @param string $tipTitle
     */
    public function aTipIsEnabledOnConfirmationPhoneContextForThisType($tipTitle)
    {
        $type = $this->tipContextProxy->getStorage()->get('type');

        if (!$type instanceof Type) {
            throw new \LogicException('Missing Type');
        }

        $this->aTipIsEnabledOn($tipTitle, $type, [TipFactory::ON_CONFIRMATION_PHONE => true]);
    }

    /**
     * @param string $tipTitle
     * @param Type   $type
     * @param array  $contexts
     */
    private function aTipIsEnabledOn($tipTitle, Type $type, array $contexts)
    {
        $tip = $this->tipContextProxy->getTipManager()->affectToType($tipTitle, $type, $contexts);
        $this->tipContextProxy->getStorage()->set('tip', $tip);
    }
}
