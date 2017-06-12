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
        $this->thereIsASheetWithTheTitle(null);
    }

    /**
     * @Given /^there is a sheet with the title "(?P<title>[^"]+)"$/
     *
     * @param string|null $title
     */
    public function thereIsASheetWithTheTitle($title)
    {
        $event = $this->sheetContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $sheet = $this->sheetContextProxy->getSheetManager()->create($event, null, null, $title);
        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }

    /**
     * @Given /^there is a sheet in this group with the title "(?P<title>[^"]+)"$/
     *
     * @param string|null $title
     */
    public function thereIsASheetInThisGroup($title)
    {
        $event = $this->sheetContextProxy->getStorage()->get('event');
        $group = $this->sheetContextProxy->getStorage()->get('group');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $group) {
            throw new \InvalidArgumentException('Missing Group');
        }

        $sheet = $this->sheetContextProxy->getSheetManager()->create($event, null, null, $title, $group);

        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }
}
