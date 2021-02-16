<?php

namespace Proximum\Vimeet\Tests\Application\Command\Type\Content;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Type\Content\DefineTermsOfSale;
use Proximum\Vimeet\Application\Command\Type\Content\DefineTermsOfSaleHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface;

class DefineTermsOfSaleHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event, $type, $eventContent, $contentRepository;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->type->getEvent()->willReturn($this->event->reveal());
        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->eventContent = $this->prophesize(Event\Content::class);
        $this->contentRepository = $this->prophesize(ContentRepositoryInterface::class);
    }

    public function testNotEnabledAndNoPreviousContent()
    {
        $this->eventContent->getValue('fr')->shouldBeCalled()->willReturn('Article Premier :');
        $this->eventContent->getValue('en')->shouldBeCalled()->willReturn('First Article:');

        $command = new DefineTermsOfSale($this->type->reveal(), $this->eventContent->reveal());
        $handler = new DefineTermsOfSaleHandler($this->contentRepository->reveal());

        $this->contentRepository->remove(Argument::any())->shouldNotBeCalled();
        $this->contentRepository->add(Argument::any())->shouldNotBeCalled();
        $this->contentRepository->update(Argument::any())->shouldNotBeCalled();

        $handler->handle($command);
    }

    public function testNotEnabledWithPreviousContent()
    {
        $this->eventContent->getValue('fr')->shouldNotBeCalled();
        $this->eventContent->getValue('en')->shouldNotBeCalled();
        $content = $this->prophesize(Type\Content::class);
        $content->getValue('fr')->shouldBeCalled()->willReturn('Article Premier :');
        $content->getValue('en')->shouldBeCalled()->willReturn('First Article:');

        $command = new DefineTermsOfSale($this->type->reveal(), $this->eventContent->reveal(), $content->reveal());
        $command->enabled = false;
        $handler = new DefineTermsOfSaleHandler($this->contentRepository->reveal());

        $this->contentRepository->remove($content->reveal())->shouldBeCalled();
        $this->contentRepository->add(Argument::any())->shouldNotBeCalled();
        $this->contentRepository->update(Argument::any())->shouldNotBeCalled();

        $handler->handle($command);
    }

    public function testCreate()
    {
        $this->eventContent->getValue('fr')->shouldBeCalled()->willReturn('Article Premier :');
        $this->eventContent->getValue('en')->shouldBeCalled()->willReturn('First Article:');

        $command = new DefineTermsOfSale($this->type->reveal(), $this->eventContent->reveal(), null);
        $command->enabled = true;
        $command->translations = [
            'fr' => [
                'value' => 'Premier article :',
            ],
            'en' => [
                'value' => 'Article 1 :',
            ]
        ];
        $handler = new DefineTermsOfSaleHandler($this->contentRepository->reveal());

        $this->contentRepository->remove(Argument::any())->shouldNotBeCalled();
        $this->contentRepository->update(Argument::any())->shouldNotBeCalled();

        $content = new Type\Content($this->type->reveal(), Type\Content::TYPE_TERMS_OF_SALE);
        $content->translate('fr', 'Premier article :');
        $content->translate('en', 'Article 1 :');
        $this->contentRepository->add($content)->shouldBeCalled();

        $handler->handle($command);
    }

    public function testUpdate()
    {
        $this->eventContent->getValue('fr')->shouldNotBeCalled();
        $this->eventContent->getValue('en')->shouldNotBeCalled();

        $content = $this->prophesize(Type\Content::class);
        $content->getValue('fr')->shouldBeCalled()->willReturn('Article Premier :');
        $content->getValue('en')->shouldBeCalled()->willReturn('First Article:');
        $content->translate('fr', 'Premier article :')->shouldBeCalled();
        $content->translate('en', 'Article 1 :')->shouldBeCalled();

        $command = new DefineTermsOfSale($this->type->reveal(), $this->eventContent->reveal(), $content->reveal());
        $command->enabled = true;
        $command->translations = [
            'fr' => [
                'value' => 'Premier article :',
            ],
            'en' => [
                'value' => 'Article 1 :',
            ]
        ];
        $handler = new DefineTermsOfSaleHandler($this->contentRepository->reveal());

        $this->contentRepository->remove(Argument::any())->shouldNotBeCalled();
        $this->contentRepository->add(Argument::any())->shouldNotBeCalled();
        $this->contentRepository->update($content->reveal())->shouldBeCalled();

        $handler->handle($command);
    }
}
