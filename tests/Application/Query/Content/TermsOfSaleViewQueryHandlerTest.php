<?php

namespace Proximum\Vimeet\Tests\Application\Query\Content;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Model\Type\Content as TypeContent;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface as TypeContentRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\Markdown;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TermsOfSaleViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $locale = 'fr';

        $content = new Content($event, 'terms-of-sale');
        $content->translate($locale, 'foobar');

        $markdown = $this->prophesize(Markdown::class);
        $markdown->toHtml($content->getValue($locale))->shouldBeCalled()->willReturn('barfoo');

        $typeContentRepository = $this->prophesize(TypeContentRepositoryInterface::class);
        $typeContentRepository
            ->findByTypeAndAssociatedParticipationType(TypeContent::TYPE_TERMS_OF_SALE, $type->reveal())
            ->shouldBeCalled()
            ->willReturn(null);
        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository
            ->findByEventAndType($event, Content::TYPE_TERMS_OF_SALE)
            ->shouldBeCalled()
            ->willReturn($content)
        ;

        $result = (new TermsOfSaleViewQueryHandler(
            $contentRepository->reveal(),
            $typeContentRepository->reveal(),
            $markdown->reveal()
        ))->handle(new TermsOfSaleViewQuery($event, $sheet->reveal(), $locale));

        $this->assertEquals('barfoo', $result->content);
    }

    public function testHandleOverridedByType()
    {
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $typeContent = $this->prophesize(TypeContent::class);
        $typeContent->getValue($locale)->shouldBeCalled()->willReturn('<p>barfoo</p>');
        $markdown = $this->prophesize(Markdown::class);

        $typeContentRepository = $this->prophesize(TypeContentRepositoryInterface::class);
        $typeContentRepository
            ->findByTypeAndAssociatedParticipationType(TypeContent::TYPE_TERMS_OF_SALE, $type->reveal())
            ->shouldBeCalled()
            ->willReturn($typeContent);
        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository
            ->findByEventAndType($event, Content::TYPE_TERMS_OF_SALE)
            ->shouldNotBeCalled()
        ;

        $result = (new TermsOfSaleViewQueryHandler(
            $contentRepository->reveal(),
            $typeContentRepository->reveal(),
            $markdown->reveal()
        ))->handle(new TermsOfSaleViewQuery($event, $sheet->reveal(), $locale));

        $this->assertEquals('<p>barfoo</p>', $result->content);
    }
}
