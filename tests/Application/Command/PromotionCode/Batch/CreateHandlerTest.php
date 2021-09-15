<?php

namespace Proximum\Vimeet\Tests\Application\Command\PromotionCode\Batch;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\PromotionCode\Batch\Create;
use Proximum\Vimeet\Application\Command\PromotionCode\Batch\CreateHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\PromotionCode\PromotionCodeFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Promotion\Generator\CodeGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    /** @var CreateHandler */
    private $createHandler;

    /** @var ObjectProphecy|CodeGeneratorInterface */
    private $codeGenerator;

    /** @var ObjectProphecy|PromotionCodeFactory */
    private $promotionCodeFactory;

    /** @var ObjectProphecy|PromotionCodeGroupRepositoryInterface */
    private $promotionCodeGroupRepository;

    /** @var ObjectProphecy|PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy|Event */
    private $event;

    protected function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->getLocales()->willReturn(['fr', 'en']);

        $this->codeGenerator = $this->prophesize(CodeGeneratorInterface::class);
        $this->promotionCodeFactory = $this->prophesize(PromotionCodeFactory::class);
        $this->promotionCodeGroupRepository = $this->prophesize(PromotionCodeGroupRepositoryInterface::class);
        $this->promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $this->dateTime = new \DateTime();

        $this->createHandler = new CreateHandler(
            $this->codeGenerator->reveal(),
            $this->promotionCodeFactory->reveal(),
            $this->promotionCodeGroupRepository->reveal(),
            $this->promotionCodeRepository->reveal(),
            $this->dateTime
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

        $create = new Create($this->event->reveal());
        $create->number = 2;
        $create->title = 'MyPromotionCode group';
        $create->prefix = 'PRECODE-';
        $create->translations = $translations;
        $create->promotions = $promotions;

        $expectedPromotionCodeGroup = new PromotionCodeGroup(
            $this->event->reveal(),
            'MyPromotionCode group',
            2,
            'PRECODE-',
            null,
            null,
            $this->dateTime
        );
        $this->promotionCodeGroupRepository->add($expectedPromotionCodeGroup)->shouldBeCalled();

        $this->codeGenerator
            ->generate($this->event->reveal(), 'PRECODE-')
            ->shouldBeCalledTimes(2)
            ->willReturn('PRECODE-AAAAA', 'PRECODE-BBBBB')
        ;

        $promotionCode1 = new PromotionCode(
            $this->event->reveal(),
            'MyPromotionCode group',
            'PRECODE-AAAAA',
            null,
            null,
            $expectedPromotionCodeGroup
        );
        $this->promotionCodeFactory
            ->create(
                $this->event->reveal(),
                'MyPromotionCode group',
                'PRECODE-AAAAA',
                null,
                null,
                $translations,
                $promotions,
                $expectedPromotionCodeGroup
            )
            ->shouldBeCalled()
            ->willReturn($promotionCode1)
        ;
        $this->promotionCodeRepository->add($promotionCode1)->shouldBeCalled();

        $promotionCode2 = new PromotionCode(
            $this->event->reveal(),
            'MyPromotionCode group',
            'PRECODE-BBBBB',
            null,
            null,
            $expectedPromotionCodeGroup
        );
        $this->promotionCodeFactory
            ->create(
                $this->event->reveal(),
                'MyPromotionCode group',
                'PRECODE-BBBBB',
                null,
                null,
                $translations,
                $promotions,
                $expectedPromotionCodeGroup
            )
            ->shouldBeCalled()
            ->willReturn($promotionCode2)
        ;
        $this->promotionCodeRepository->add($promotionCode2)->shouldBeCalled();

        $this->createHandler->handle($create);
    }
}
