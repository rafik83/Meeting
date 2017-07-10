<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Meeting;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Meeting\RequestContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\MeetingContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\SheetContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\UserContextProxyInterface;

class RequestContext implements Context
{
    /**
     * @var SheetContextProxyInterface
     */
    private $sheetContextProxy;

    /**
     * @var UserContextProxyInterface
     */
    private $userContextProxy;

    /**
     * @var MeetingContextProxyInterface
     */
    private $meetingContextProxy;

    /**
     * RequestContext constructor.
     *
     * @param MeetingContextProxyInterface $meetingContextProxy
     * @param UserContextProxyInterface    $userContextProxy
     * @param SheetContextProxyInterface   $sheetContextProxy
     */
    public function __construct(
        MeetingContextProxyInterface $meetingContextProxy,
        UserContextProxyInterface $userContextProxy,
        SheetContextProxyInterface $sheetContextProxy
    ) {
        $this->sheetContextProxy   = $sheetContextProxy;
        $this->userContextProxy    = $userContextProxy;
        $this->meetingContextProxy = $meetingContextProxy;
    }

    /**
     * @Given /^there is a request between "(?P<fromSheet>[^"]+)" and "(?P<toSheet>[^"]+)"$/
     *
     * @param string $fromSheet
     * @param string $toSheet
     */
    public function thereIsARequestBetweenTwoSheets(string $fromSheet, string $toSheet)
    {
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $fromSheet = $this->sheetContextProxy->getSheetManager()
            ->create($event, null, null, $fromSheet);

        $toSheet = $this->sheetContextProxy->getSheetManager()
            ->create($event, null, null, $toSheet);

        $request = $this->meetingContextProxy->getMeetingManager()
            ->createMeetingRequest($event, $fromSheet, [], $toSheet, []);

        $this->meetingContextProxy->getStorage()->set('request', $request);
    }
}
