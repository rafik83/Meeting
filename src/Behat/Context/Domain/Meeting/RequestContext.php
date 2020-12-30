<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Meeting;

use Behat\Behat\Context\Context;
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
     * @Given /^there is a request between "(?P<fromSheetTitle>[^"]+)" and "(?P<toSheetTitle>[^"]+)"$/
     *
     * @param string $fromSheetTitle
     * @param string $toSheetTitle
     */
    public function thereIsARequestBetweenTwoSheets(string $fromSheetTitle, string $toSheetTitle)
    {
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $fromSheet = $this->sheetContextProxy->getSheetManager()
            ->getSheetByEventAndTitle($event, $fromSheetTitle);

        if (null === $fromSheet) {
            $fromSheet = $this->sheetContextProxy->getSheetManager()
                ->create($event, null, null, $fromSheetTitle);
        }

        $toSheet = $this->sheetContextProxy->getSheetManager()
            ->getSheetByEventAndTitle($event, $toSheetTitle);

        if (null === $toSheet) {
            $toSheet = $this->sheetContextProxy->getSheetManager()
                ->create($event, null, null, $toSheetTitle);
        }

        $request = $this->meetingContextProxy->getMeetingManager()
            ->createMeetingRequest($event, $fromSheet, [], $toSheet, []);

        $this->meetingContextProxy->getStorage()->set('request', $request);
    }
}
