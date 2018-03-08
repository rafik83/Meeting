<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Content;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\Markdown;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TermsOfSaleViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $locale = 'fr';

        $content = new Content($event, 'terms-of-sale');
        $content->translate($locale, 'foobar');

        $markdown = $this->prophesize(Markdown::class);
        $markdown->toHtml($content->getValue($locale))->shouldBeCalled()->willReturn('barfoo');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository
            ->findByEventAndType($event, Content::TYPE_TERMS_OF_SALE)
            ->shouldBeCalled()
            ->willReturn($content)
        ;

        $result = (new TermsOfSaleViewQueryHandler(
            $contentRepository->reveal(),
            $markdown->reveal()
        ))->handle(new TermsOfSaleViewQuery($event, $locale));

        $this->assertEquals('barfoo', $result->content);
    }
}
