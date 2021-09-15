<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Tip\Condition\ConditionInterface;
use Proximum\Vimeet\Application\Query\Tip\IsTipOpened;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class TipTranslationViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event = EventFactory::createEvent();
        $sheet = SheetFactory::create($event);
        $user = UserFactory::create();

        $tip = new Tip('tip', null, true, true, true, false, false, false, false, false, false, false, $dateTime);
        $tip->setTranslation('fr', 'title', 'content', $dateTime);

        $tip2 = new Tip('tip2', null, true, true, true, false, false, false, false, false, false, false, $dateTime);
        $tip2->setTranslation('fr', 'title2', 'content2', $dateTime);

        $tipTranslationView1 = new TipTranslationView(1, 'title', 'content', 'Title 1', Tip::DISPLAY_DEFAULT);
        $tipTranslationView2 = new TipTranslationView(2, 'title1', 'content2', 'Title 2', Tip::DISPLAY_DEFAULT);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $query = new TipTranslationViewQuery($sheet, $user, 'event_catalog_index', 'fr');

        $condition1 = $this->prophesize(ConditionInterface::class);
        $condition1->isSatisfiedBy($query, $tipTranslationView1)->shouldBeCalled()->willReturn(true);
        $condition1->isSatisfiedBy($query, $tipTranslationView2)->shouldBeCalled()->willReturn(true);

        $condition2 = $this->prophesize(ConditionInterface::class);
        $condition2->isSatisfiedBy($query, $tipTranslationView1)->shouldBeCalled()->willReturn(false);
        $condition2->isSatisfiedBy($query, $tipTranslationView2)->shouldBeCalled()->willReturn(true);

        $condition3 = $this->prophesize(ConditionInterface::class);
        $condition3->isSatisfiedBy($query, $tipTranslationView1)->shouldNotBeCalled();
        $condition3->isSatisfiedBy($query, $tipTranslationView2)->shouldBeCalled()->willReturn(true);

        $isTipOpened = $this->prophesize(IsTipOpened::class);
        $isTipOpened->isSatisfiedBy($query, $tipTranslationView1)->shouldNotBeCalled();
        $isTipOpened->isSatisfiedBy($query, $tipTranslationView2)->shouldBeCalled()->willReturn(true);

        $handler = new TipTranslationViewQueryHandler(
            $tipRepository->reveal(),
            $isTipOpened->reveal(),
            [$condition1->reveal(), $condition2->reveal(), $condition3->reveal()]
        );

        $tipRepository
            ->getByContextAndEventAndType(
                $query->event,
                $query->type,
                'onCatalog',
                $query->locale
            )
            ->shouldBeCalled()
            ->willReturn([$tipTranslationView1, $tipTranslationView2]);

        $expectedTipTranslationView = new TipTranslationView(2, 'title1', 'content2', 'Title 2', Tip::DISPLAY_DEFAULT);
        $expectedTipTranslationView->isOpened = true;

        $this->assertEquals([$expectedTipTranslationView], $handler->handle($query));
    }
}
