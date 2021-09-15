<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Happening;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Happening\CategoryContextProxyInterface;

class CategoryContext implements Context
{
    /** @var CategoryContextProxyInterface */
    private $categoryContextProxy;

    public function __construct(CategoryContextProxyInterface $categoryContextProxy)
    {
        $this->categoryContextProxy = $categoryContextProxy;
    }

    /**
     * @Given /^there is an happening category called "(?P<title>[^"]+)" for this event$/
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
        $this->categoryContextProxy->getStorage()->set('happening_category', $category);
    }
}
