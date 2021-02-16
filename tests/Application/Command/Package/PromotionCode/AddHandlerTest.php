<?php

namespace Application\Command\Package\PromotionCode;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Package\PromotionCode\Add;
use Proximum\Vimeet\Application\Command\Package\PromotionCode\AddHandler;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeConflictException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotFoundException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotUsedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AddHandlerTest extends TestCase
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \DateTimeImmutable
     */
    private $datetime;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var Product
     */
    private $product;

    public function setUp()
    {
        $this->event    = EventFactory::createEvent();
        $this->type     = new Type($this->event);
        $this->user     = new User('email@email.com', 'salt', 'password', 'fr');
        $this->datetime = new \DateTimeImmutable();
        $this->sheet    = new Sheet($this->event, $this->type, [], $this->user, $this->datetime);
        $this->product  = Product::createOption($this->event, 'Option A', 'a.jpg', 100, 20, 2, 4, 3, false);
    }

    public function testHandle()
    {
        $promotionCode = new PromotionCode($this->event, 'Promotion Code Test', 'PROMOCODE50', 10,
            $this->datetime->modify('+1 month'));
        $promotionCode->setPromotion($this->product, Promotion::TYPE_PERCENT_OFF, 50);

        $planRow          = new CartRow($this->sheet, $this->product, 5);
        $promotionCodeRow = new PromotionCodeRow($this->sheet, $promotionCode);

        $actualCart   = new Cart($this->sheet, [$planRow], [], 1);
        $expectedCart = new Cart($this->sheet, [$planRow], [$promotionCodeRow], 1);

        $cartManager             = $this->prophesize(CartManager::class);
        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $orderMerger             = $this->prophesize(Merger::class);

        $add = new Add($this->sheet);
        $add->promotionCode = 'PROMOCODE50';

        $cartManager->getCart($this->sheet)->shouldBeCalled()->willReturn($actualCart);

        $promotionCodeRepository
            ->findByEventAndCode($this->event, 'PROMOCODE50')
            ->shouldBeCalled()
            ->willReturn($promotionCode);

        $cartManager->save($expectedCart)->shouldBeCalled();

        // Handler
        $handler = new AddHandler(
            $cartManager->reveal(),
            $promotionCodeRepository->reveal(),
            $this->datetime,
            $orderMerger->reveal()
        );
        $handler->handle($add);
    }

    public function testHandleNotFoundCode()
    {
        $this->expectException(PromotionCodeNotFoundException::class);

        $planRow      = new CartRow($this->sheet, $this->product, 5);
        $actualCart   = new Cart($this->sheet, [$planRow], [], 1);
        $expectedCart = new Cart($this->sheet, [$planRow], [], 1);

        $cartManager             = $this->prophesize(CartManager::class);
        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $orderMerger             = $this->prophesize(Merger::class);

        $add = new Add($this->sheet);
        $add->promotionCode = 'PROMO_CODE_NOT_EXIST';

        $cartManager->getCart($this->sheet)->shouldBeCalled()->willReturn($actualCart);
        $cartManager->save($expectedCart)->shouldNotBeCalled();

        $promotionCodeRepository
            ->findByEventAndCode($this->event, 'PROMO_CODE_NOT_EXIST')
            ->shouldBeCalled()
            ->willReturn(null);

        // Handler
        $handler = new AddHandler(
            $cartManager->reveal(),
            $promotionCodeRepository->reveal(),
            $this->datetime,
            $orderMerger->reveal());

        $handler->handle($add);
    }

    public function testHandleOutdatedCode()
    {
        $promotionCode = new PromotionCode($this->event, 'Promotion Code Test', 'PROMOCODE50', 10,
            $this->datetime->modify('-1 month'));
        $promotionCode->setPromotion($this->product, Promotion::TYPE_PERCENT_OFF, 50);

        $this->expectException(PromotionCodeOutDatedException::class);

        $planRow      = new CartRow($this->sheet, $this->product, 5);
        $actualCart   = new Cart($this->sheet, [$planRow], [], 1);
        $expectedCart = new Cart($this->sheet, [$planRow], [], 1);

        $cartManager             = $this->prophesize(CartManager::class);
        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $orderMerger             = $this->prophesize(Merger::class);

        $add = new Add($this->sheet);
        $add->promotionCode = 'PROMOCODE50';

        $cartManager->getCart($this->sheet)->shouldBeCalled()->willReturn($actualCart);

        $promotionCodeRepository
            ->findByEventAndCode($this->event, 'PROMOCODE50')
            ->shouldBeCalled()
            ->willReturn($promotionCode);

        $cartManager->save($expectedCart)->shouldNotBeCalled();

        // Handler
        $handler = new AddHandler(
            $cartManager->reveal(),
            $promotionCodeRepository->reveal(),
            $this->datetime,
            $orderMerger->reveal());

        $handler->handle($add);
    }

    public function testHandleAlreadyExistCode()
    {
        $this->expectException(PromotionCodeAlreadyExistException::class);

        $promotionCode = new PromotionCode($this->event, 'Promotion Code Test', 'PROMOCODE50', 10,
            $this->datetime->modify('+1 month'));
        $promotionCode->setPromotion($this->product, Promotion::TYPE_PERCENT_OFF, 50);

        $planRow          = new CartRow($this->sheet, $this->product, 5);
        $promotionCodeRow = new PromotionCodeRow($this->sheet, $promotionCode);
        $actualCart       = new Cart($this->sheet, [$planRow], [$promotionCodeRow], 1);
        $expectedCart     = new Cart($this->sheet, [$planRow], [$promotionCodeRow], 1);

        $cartManager             = $this->prophesize(CartManager::class);
        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $orderMerger             = $this->prophesize(Merger::class);

        $add = new Add($this->sheet);
        $add->promotionCode = 'PROMOCODE50';

        $cartManager->getCart($this->sheet)->shouldBeCalled()->willReturn($actualCart);

        $promotionCodeRepository
            ->findByEventAndCode($this->event, 'PROMOCODE50')
            ->shouldBeCalled()
            ->willReturn($promotionCode);

        $cartManager->save($expectedCart)->shouldNotBeCalled();

        // Handler
        $handler = new AddHandler(
            $cartManager->reveal(),
            $promotionCodeRepository->reveal(),
            $this->datetime,
            $orderMerger->reveal());

        $handler->handle($add);
    }

    public function testHandleNotUsedCode()
    {
        $this->expectException(PromotionCodeNotUsedException::class);

        $promotionCode = new PromotionCode($this->event, 'Promotion Code Test', 'PROMOCODE50', 10,
            $this->datetime->modify('+1 month'));
        $promotionCode->setPromotion($this->product, Promotion::TYPE_PERCENT_OFF, 50);

        $otherProductNotConcerned = Product::createOption($this->event, 'Option B', 'a.jpg', 100, 20, 2, 4, 3, false);

        $planRow      = new CartRow($this->sheet, $otherProductNotConcerned, 5);
        $actualCart   = new Cart($this->sheet, [$planRow], [], 1);
        $expectedCart = new Cart($this->sheet, [$planRow], [], 1);

        $cartManager             = $this->prophesize(CartManager::class);
        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $orderMerger             = $this->prophesize(Merger::class);

        $add = new Add($this->sheet);
        $add->promotionCode = 'PROMOCODE50';

        $cartManager->getCart($this->sheet)->shouldBeCalled()->willReturn($actualCart);

        $promotionCodeRepository
            ->findByEventAndCode($this->event, 'PROMOCODE50')
            ->shouldBeCalled()
            ->willReturn($promotionCode);

        $cartManager->save($expectedCart)->shouldNotBeCalled();

        // Handler
        $handler = new AddHandler(
            $cartManager->reveal(),
            $promotionCodeRepository->reveal(),
            $this->datetime,
            $orderMerger->reveal());

        $handler->handle($add);
    }

    public function testHandleCodeConflict()
    {
        $this->expectException(PromotionCodeConflictException::class);

        $promotionCode = new PromotionCode($this->event, 'Promotion Code Test', 'PROMOCODE50', 10,
            $this->datetime->modify('+1 month'));
        $promotionCode->setPromotion($this->product, Promotion::TYPE_PERCENT_OFF, 50);

        $promotionCodeTwo = new PromotionCode($this->event, 'Promotion Code Test', 'PROMOCODE10', 10,
            $this->datetime->modify('+1 month'));
        $promotionCodeTwo->setPromotion($this->product, Promotion::TYPE_VALUE_OFF, 10);

        $planRow          = new CartRow($this->sheet, $this->product, 5);
        $promotionCodeRow = new PromotionCodeRow($this->sheet, $promotionCode);
        $actualCart       = new Cart($this->sheet, [$planRow], [$promotionCodeRow], 1);
        $expectedCart     = new Cart($this->sheet, [$planRow], [], 1);

        $cartManager             = $this->prophesize(CartManager::class);
        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $orderMerger             = $this->prophesize(Merger::class);

        $add = new Add($this->sheet);
        $add->promotionCode = 'PROMOCODE10';

        $cartManager->getCart($this->sheet)->shouldBeCalled()->willReturn($actualCart);

        $promotionCodeRepository
            ->findByEventAndCode($this->event, 'PROMOCODE10')
            ->shouldBeCalled()
            ->willReturn($promotionCodeTwo);

        $cartManager->save($expectedCart)->shouldNotBeCalled();

        // Handler
        $handler = new AddHandler(
            $cartManager->reveal(),
            $promotionCodeRepository->reveal(),
            $this->datetime,
            $orderMerger->reveal());

        $handler->handle($add);
    }
}
