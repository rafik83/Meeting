<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

        $catalogVisibility = $this->catalogVisibilityContextProxy->getCatalogVisibilityManager()->create($event);

        $this->catalogVisibilityContextProxy->getStorage()->set('catalogVisibility', $catalogVisibility);

    }
}
