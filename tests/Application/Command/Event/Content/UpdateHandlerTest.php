<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Content;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\Content\Update;
use Proximum\Vimeet\Application\Command\Event\Content\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
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

        $update = new Update($content);
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

        $handler = new UpdateHandler($contentRepository->reveal());
        $handler->handle($update);
    }
}
