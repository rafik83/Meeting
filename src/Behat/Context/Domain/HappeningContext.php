<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\HappeningContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class HappeningContext implements Context
{
    /** @var HappeningContextProxyInterface */
    private $happeningContextProxy;

    public function __construct(HappeningContextProxyInterface $happeningContextProxy)
    {
        $this->happeningContextProxy = $happeningContextProxy;
    }

    /**
     * @Given there is a webinar in this event
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
        $this->happeningContextProxy->getStorage()->set('happening', $happening);
    }

    /**
     * @Given this user participate to this happening
     */
    public function thisUserParticipateToThisHappening()
    {
        /** @var null|Happening $happening */
        $happening = $this->happeningContextProxy->getStorage()->get('happening');

        if (null === $happening) {
            throw new \InvalidArgumentException('Missing Happening');
        }

        /** @var null|User $user */
        $user = $this->happeningContextProxy->getStorage()->get('user');

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $happeningManager = $this->happeningContextProxy->getHappeningManager();
        $happeningManager->userParticipateToHappening($user, $happening);
    }
}
