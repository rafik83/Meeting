<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\CategoryContextProxyInterface;

class CategoryContext implements Context
{
    /** @var CategoryContextProxyInterface */
    private $categoryContextProxy;

    public function __construct(CategoryContextProxyInterface $categoryContextProxy)
    {
        $this->categoryContextProxy = $categoryContextProxy;
    }

    /**
     * @Given there is a participant category :title for this event
     */
    public function thereIsAParticipantCategoryForThisEvent($title)
    {
        $storage = $this->categoryContextProxy->getStorage();
        $event = $storage->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $category = $this->categoryContextProxy->getCategoryManager()->create($event, $title);
        $storage->set('categoryParticipant', $category);
    }

    /**
     * @Given this category contains all types
     */
    public function thisCategoryContainsAllTypes()
    {
        $category = $this->categoryContextProxy->getStorage()->get('categoryParticipant');

        if (null === $category) {
            throw new \InvalidArgumentException('Missing Category');
        }

        $category = $this->categoryContextProxy->getCategoryManager()->addAllTypes($category);
    }
}
