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
use Proximum\Vimeet\Behat\Context\Domain\Proxy\SheetContextProxyInterface;

class SheetContext implements Context
{
    /** @var SheetContextProxyInterface */
    private $sheetContextProxy;

    /**
     * @param SheetContextProxyInterface $sheetContextProxy
     */
    public function __construct(SheetContextProxyInterface $sheetContextProxy)
    {
        $this->sheetContextProxy = $sheetContextProxy;
    }

    /**
     * @Given there is a sheet
     */
    public function thereIsASheet()
    {
        $event = $this->sheetContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $sheet = $this->sheetContextProxy->getSheetManager()->create($event);
        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }
}
