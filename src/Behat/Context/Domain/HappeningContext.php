<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\HappeningContextProxyInterface;

class HappeningContext implements Context
{
    /** @var HappeningContextProxyInterface */
    private $happeningContextProxy;

    public function __construct(HappeningContextProxyInterface $happeningContextProxy)
    {
        $this->happeningContextProxy = $happeningContextProxy;
    }

    /**
     * @Given /^there is a webinar in this event$/
     */
    public function thereIsAWebinar()
    {
        $event = $this->happeningContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $categoryManager = $this->happeningContextProxy->getCategoryManager();
        $category = $categoryManager->create($event, 'Webinar');

        $happeningManager = $this->happeningContextProxy->getHappeningManager();
        $happening = $happeningManager->createHappening($category);
        $this->happeningContextProxy->getStorage()->set('meeting', $happening);
    }
}
