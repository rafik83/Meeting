<?php

namespace Proximum\Vimeet\Tests\Application\Command\PromotionCode\Batch;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\PromotionCode\Batch\Update;
use Proximum\Vimeet\Application\Command\PromotionCode\Batch\UpdateHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\PromotionCode\PromotionCodeFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    /** @var UpdateHandler */
    private $updateHandler;

    /** @var ObjectProphecy|PromotionCodeFactory */
    private $promotionCodeFactory;

    /** @var ObjectProphecy|PromotionCodeGroupRepositoryInterface */
    private $promotionCodeGroupRepository;

    /** @var ObjectProphecy|PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var ObjectProphecy|Event */
    private $event;

    protected function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->getLocales()->willReturn(['fr', 'en']);

        $this->promotionCodeFactory = $this->prophesize(PromotionCodeFactory::class);
        $this->promotionCodeGroupRepository = $this->prophesize(PromotionCodeGroupRepositoryInterface::class);
        $this->promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);

        $this->updateHandler = new UpdateHandler(
            $this->promotionCodeFactory->reveal(),
            $this->promotionCodeRepository->reveal(),
            $this->promotionCodeGroupRepository->reveal()
        );
    }

    public function testHandle()
    {
        $product = $this->prophesize(Product::class);
        $translations = [
            'fr' => [
                'label' => 'updated label fr',
                'description' => 'updated description fr',
            ],
            'en' => [
                'label' => 'updated label en',
                'description' => 'updated description en',
            ],
        ];
        $promotions = [
            [
                'product' => $product->reveal(),
                'type' => Promotion::TYPE_PERCENT_OFF,
                'value' => 30,
                'quantityMax' => 10,
            ]
        ];

        $previousValidUntilDate = new \DateTime('2019-09-01');
        $newValidUntilDate = new \DateTime('2019-10-15');

        $promotion = $this->prophesize(Promotion::class);
        $promotion->getProduct()->shouldBeCalled()->willReturn($product->reveal());
        $promotion->getType()->shouldBeCalled()->willReturn(Promotion::TYPE_PERCENT_OFF);
        $promotion->getValue()->shouldBeCalled()->willReturn(50);
        $promotion->getQuantityMax()->shouldBeCalled()->willReturn(1);

        $promotionCode1 = $this->prophesize(PromotionCode::class);
        $promotionCode1->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $promotionCode1->getCode()->shouldBeCalled()->willReturn('PRECODE-AAAAA');
        $promotionCode1->getLabel('fr')->shouldBeCalled()->willReturn('title fr');
        $promotionCode1->getDescription('fr')->shouldBeCalled()->willReturn('description fr');
        $promotionCode1->getLabel('en')->shouldBeCalled()->willReturn('title en');
        $promotionCode1->getDescription('en')->shouldBeCalled()->willReturn('description en');
        $promotionCode1->getPromotions()->shouldBeCalled()->willReturn([$promotion->reveal()]);

        $promotionCode2 = $this->prophesize(PromotionCode::class);
        $promotionCode2->getCode()->shouldBeCalled()->willReturn('PRECODE-BBBBB');

        $promotionCodeGroup = $this->prophesize(PromotionCodeGroup::class);
        $promotionCodeGroup->getTitle()->shouldBeCalled()->willReturn('MyPromotionCode group');
        $promotionCodeGroup->getStock()->shouldBeCalled()->willReturn(2);
        $promotionCodeGroup->getValidUntil()->shouldBeCalled()->willReturn($previousValidUntilDate);
        $promotionCodeGroup->getPromotionCodes()->shouldBeCalled()->willReturn(
            [
                $promotionCode1->reveal(),
                $promotionCode2->reveal(),
            ]
        );
        $promotionCodeGroup
            ->update('MyPromotionCode group updated', null, $newValidUntilDate)
            ->shouldBeCalled();

        $update = new Update($promotionCodeGroup->reveal());
        $update->title = 'MyPromotionCode group updated';
        $update->translations = $translations;
        $update->promotions = $promotions;
        $update->stock = null;
        $update->validUntil = $newValidUntilDate;

        $this->promotionCodeGroupRepository->set($promotionCodeGroup->reveal())->shouldBeCalled();

        $this->promotionCodeFactory
            ->update(
                $promotionCode1->reveal(),
                'MyPromotionCode group updated',
                'PRECODE-AAAAA',
                null,
                $newValidUntilDate,
                $translations,
                $promotions
            )
            ->shouldBeCalled()
            ->willReturn($promotionCode1->reveal());

        $this->promotionCodeRepository->set($promotionCode1->reveal())->shouldBeCalled();

        $this->promotionCodeFactory
            ->update(
                $promotionCode2->reveal(),
                'MyPromotionCode group updated',
                'PRECODE-BBBBB',
                null,
                $newValidUntilDate,
                $translations,
                $promotions
            )
            ->shouldBeCalled()
            ->willReturn($promotionCode2->reveal());

        $this->promotionCodeRepository->set($promotionCode2->reveal())->shouldBeCalled();

        $this->updateHandler->handle($update);
    }
}
