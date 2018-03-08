<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\TypeContextProxyInterface;

class TypeContext implements Context
{
    /** @var TypeContextProxyInterface */
    private $typeContextProxy;

    /**
     * @param TypeContextProxyInterface $typeContextProxy
     */
    public function __construct(TypeContextProxyInterface $typeContextProxy)
    {
        $this->typeContextProxy = $typeContextProxy;
    }

    /**
     * @Given /^there is a type in this event$/
     */
    public function createInEvent()
    {
        $event = $this->typeContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $type = $this->typeContextProxy->getTypeManager()->create($event);
        $this->typeContextProxy->getStorage()->set('type', $type);
    }

    /**
     * @Given /^this package is assigned to this type$/
     */
    public function assignPackage()
    {
        $type = $this->typeContextProxy->getStorage()->get('type');
        $package = $this->typeContextProxy->getStorage()->get('package');

        if (null === $type) {
            throw new \InvalidArgumentException('Missing Type');
        }

        if (null === $package) {
            throw new \InvalidArgumentException('Missing Package');
        }

        $this->typeContextProxy->getTypeManager()->assignPackageToType($type, $package);
    }
}
