<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\CatalogVisibilityContextProxyInterface;

class CatalogVisibilityContext implements Context
{
    /**
     * @var CatalogVisibilityContextProxyInterface
     */
    private $catalogVisibilityContextProxy;

    /**
     * CatalogVisibilityContext constructor.
     *
     * @param CatalogVisibilityContextProxyInterface $catalogVisibilityContextProxy
     */
    public function __construct(CatalogVisibilityContextProxyInterface $catalogVisibilityContextProxy)
    {
        $this->catalogVisibilityContextProxy = $catalogVisibilityContextProxy;
    }

    /**
     * @Given /^the catalog visibility is configured$/
     */
    public function createCatalogVisibility()
    {
        $event = $this->catalogVisibilityContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $catalogVisibility = $this->catalogVisibilityContextProxy
            ->getCatalogVisibilityManager()
            ->create($event);

        $this->catalogVisibilityContextProxy->getStorage()->set('catalogVisibility', $catalogVisibility);
    }

    /**
     * @Given /^Allow all types to be visible on catalog visibility$/
     */
    public function allowAllTypesToBeVisible()
    {
        $catalogVisibility = $this->catalogVisibilityContextProxy->getStorage()->get('catalogVisibility');

        if (null === $catalogVisibility) {
            throw new \InvalidArgumentException('Missing catalog visibility');
        }

        $this->catalogVisibilityContextProxy
            ->getCatalogVisibilityManager()
            ->allowAllTypesToBeVisible($catalogVisibility);
    }

    /**
     * @Given /^the catalog visibility registration url is "(?P<registrationUrl>[^"]+)"$/
     *
     * @param string $registrationUrl
     */
    public function setCatalogVisibilityRegistrationUrl(string $registrationUrl)
    {
        $catalogVisibility = $this->catalogVisibilityContextProxy->getStorage()->get('catalogVisibility');

        if (null === $catalogVisibility) {
            throw new \InvalidArgumentException('Missing catalog visibility');
        }

        $this->catalogVisibilityContextProxy
            ->getCatalogVisibilityManager()
            ->setRegistrationUrl($catalogVisibility, $registrationUrl);
    }

    /**
     * @Given /^this type is visible in catalog$/
     */
    public function typeIsVisibleInCatalog()
    {
        $type              = $this->catalogVisibilityContextProxy->getStorage()->get('type');
        $catalogVisibility = $this->catalogVisibilityContextProxy->getStorage()->get('catalogVisibility');

        if (null === $type) {
            throw new \InvalidArgumentException('Missing Type');
        }

        if (null === $catalogVisibility) {
            throw new \InvalidArgumentException('Missing CatalogVisibility');
        }

        $this->catalogVisibilityContextProxy->getCatalogVisibilityManager()->setVisibleType($catalogVisibility, $type);
    }
}
