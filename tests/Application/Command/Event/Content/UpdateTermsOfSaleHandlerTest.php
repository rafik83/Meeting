<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\Content;

use Proximum\Vimeet\Application\Command\Event\Content\UpdateTermsOfSale;
use Proximum\Vimeet\Application\Command\Event\Content\UpdateTermsOfSaleHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;

class UpdateTermsOfSaleHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = new Event();
        $content = new Event\Content($event, Event\Content::TYPE_TERMS_OF_SALE);
        $content->translate('fr', 'test');
        $content->translate('en', 'foo');

        // Expected
        $expectedContent = new Event\Content($event, Event\Content::TYPE_TERMS_OF_SALE);
        $expectedContent->translate('fr', 'sup');
        $expectedContent->translate('en', 'sop');
        $expectedContent->translate('es', 'sep');

        // Mock
        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->set($expectedContent)->shouldBeCalled();

        $update = new UpdateTermsOfSale($content);
        $update->translations = [
            'fr' => [
                'value' => 'sup',
            ],
            'en' => [
                'value' => 'sop',
            ],
            'es' => [
                'value' => 'sep',
            ],
        ];

        $handler = new UpdateTermsOfSaleHandler($contentRepository->reveal());
        $handler->handle($update);
    }
}
