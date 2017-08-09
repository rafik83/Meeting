<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

class EventUrlGeneratorTest extends TestCase
{
    public function testHandle()
    {
        $scheme = 'https';
        $event  = $this->prophesize(Event::class);
        $event->getDomain()->willReturn('vimeet');

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);

        $urlGenerator->generate('event_sheet', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('.proximum.events');

        $eventUrlGenerator = new EventUrlGenerator($scheme, $urlGenerator->reveal());

        $generatedUrl = $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            'event_sheet',
            ['sheet' => 1]
        );

        $this->assertEquals($scheme . '://vimeet.proximum.events', $generatedUrl);
    }
}
