<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Event;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Event\ContentContextProxyInterface;

class ContentContext implements Context
{
    /** @var ContentContextProxyInterface */
    private $contentContextProxy;

    /**
     * @param ContentContextProxyInterface $contentContextProxy
     */
    public function __construct(ContentContextProxyInterface $contentContextProxy)
    {
        $this->contentContextProxy = $contentContextProxy;
    }

    /**
     * @Given /^there is terms of sale for this event$/
     */
    public function thereIsTermsOfSaleForThisEvent()
    {
        $event = $this->contentContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $day = $this->contentContextProxy->getContentManager()->createTermsOfSale($event);
        $this->contentContextProxy->getStorage()->set('content', $day);
    }
}
