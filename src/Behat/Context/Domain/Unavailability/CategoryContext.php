<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Unavailability;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Unavailability\CategoryContextProxyInterface;

class CategoryContext implements Context
{
    /** @var CategoryContextProxyInterface */
    private $categoryContextProxy;

    public function __construct(CategoryContextProxyInterface $categoryContextProxy)
    {
        $this->categoryContextProxy = $categoryContextProxy;
    }

    /**
     * @Given /^there is a mass unavailability category called "(?P<title>[^"]+)" for this event$/
     *
     * @param string $title
     */
    public function createInEvent(string $title): void
    {
        $event = $this->categoryContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $category = $this->categoryContextProxy->getCategoryManager()->create($event, $title);
        $this->categoryContextProxy->getStorage()->set('mass_unavailability_category', $category);
    }
}
