<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package\OptionsValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class OptionsValidatorTest extends TestCase
{
    /** @var \DateTime $dateTime */
    private $dateTime;

    /** @var Product $product */
    private $product;

    /** @var Package $package */
    private $package;

    /** @var Sheet $sheet */
    private $sheet;

    /** @var CartManager $cartManager */
    private $cartManager;

    /** @var ExecutionContextInterface $executionContext */
    private $executionContext;

    /** @var Constraint $constraint */
    private $constraint;

    public function setUp()
    {
        $this->dateTime = new \DateTime();

        $this->product = $this->prophesize(Product::class);
        $this->product
            ->getId()
            ->shouldBeCalled()
            ->willReturn(111);

        $this->package = $this->prophesize(Package::class);
        $this->package
            ->getAvailablesOptions($this->dateTime)
            ->shouldBeCalled()
            ->willReturn([$this->product->reveal()]);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet
            ->getPackage()
            ->shouldBeCalled()
            ->willReturn($this->package->reveal());

        $cart = $this->prophesize(Cart::class);
        $this->cartManager = $this->prophesize(CartManager::class);
        $this->cartManager
            ->getCart($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($cart->reveal());

        $this->executionContext = $this->prophesize(ExecutionContextInterface::class);
        $this->constraint = $this->prophesize(Constraint::class);
    }

    public function testValidateQuantityMinPromotionCode(): void
    {
        $this->product
            ->isAttributable()
            ->willReturn(false);

        $this->sheet
            ->hasNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn(false);

        $templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);
        $templateProductGuesser
            ->guessProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $this->prophesize(
            ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode::class
        );
        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
            ->hasConflict($this->sheet->reveal(), $this->product->reveal(), 1, null)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $constraintViolationBuilder1 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->buildViolation('package.product.quantityMinPromotionCode')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->atPath('111.quantity')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->addViolation()
            ->shouldBeCalled();

        $quantityMaxGuesser = $this->prophesize(QuantityMaxGuesser::class);
        $quantityMaxGuesser
            ->getMaxByProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(2);

        $selectOptions = new SelectOptions($this->sheet->reveal(), 3);
        $selectOptions->options = [
            111 => new OptionRow(1, [], false),
        ];

        $optionValidator = new OptionsValidator(
            $quantityMaxGuesser->reveal(),
            $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->reveal(),
            $templateProductGuesser->reveal(),
            $this->dateTime,
            $this->prophesize(Merger::class)->reveal(),
            $this->cartManager->reveal()
        );
        $optionValidator->initialize($this->executionContext->reveal());
        $optionValidator->validate($selectOptions, $this->constraint->reveal());
    }

    public function testValidateQuantityNotMatch(): void
    {
        $this->product
            ->isAttributable()
            ->willReturn(false);

        $this->sheet
            ->hasNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn(false);

        $templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);
        $templateProductGuesser
            ->guessProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $this->prophesize(
            ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode::class
        );
        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
            ->hasConflict($this->sheet->reveal(), $this->product->reveal(), 3, null)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $constraintViolationBuilder1 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->buildViolation('package.product.quantityNotMatch')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->atPath('111.quantity')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->setParameters(['%min%' => 0, '%max%' => 2])
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->addViolation()
            ->shouldBeCalled();

        $quantityMaxGuesser = $this->prophesize(QuantityMaxGuesser::class);
        $quantityMaxGuesser
            ->getMaxByProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(2);

        $selectOptions = new SelectOptions($this->sheet->reveal(), 3);
        $selectOptions->options = [
            111 => new OptionRow(3, [], false),
        ];

        $optionValidator = new OptionsValidator(
            $quantityMaxGuesser->reveal(),
            $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->reveal(),
            $templateProductGuesser->reveal(),
            $this->dateTime,
            $this->prophesize(Merger::class)->reveal(),
            $this->cartManager->reveal()
        );
        $optionValidator->initialize($this->executionContext->reveal());
        $optionValidator->validate($selectOptions, $this->constraint->reveal());
    }

    public function testValidateProductNotDeletable(): void
    {
        $this->product
            ->isAttributable()
            ->willReturn(false);

        $this->product
            ->isDeletable($this->dateTime)
            ->shouldBeCalled()
            ->willReturn(false);

        $order = $this->prophesize(Order::class);
        $this->sheet
            ->hasNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn(true);
        $this->sheet
            ->getNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn([$order->reveal()]);

        $orderMerger = $this->prophesize(Merger::class);
        $orderRow = $this->prophesize(Order\Row::class);
        $orderRow
            ->getQuantity()
            ->shouldBeCalled()
            ->willReturn(3);
        $orderMerger->merge([$order->reveal()])
            ->shouldBeCalled()
            ->willReturn($order->reveal());
        $order
            ->getRowForProduct($this->product->reveal())
            ->shouldBeCalled()
            ->willReturn($orderRow);

        $templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);
        $templateProductGuesser
            ->guessProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $this->prophesize(
            ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode::class
        );
        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
            ->hasConflict($this->sheet->reveal(), $this->product->reveal(), 1, $order->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $constraintViolationBuilder1 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->buildViolation('package.product.productNotDeletable')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->atPath('111.quantity')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->addViolation()
            ->shouldBeCalled();

        $quantityMaxGuesser = $this->prophesize(QuantityMaxGuesser::class);
        $quantityMaxGuesser
            ->getMaxByProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(2);

        $selectOptions = new SelectOptions($this->sheet->reveal(), 3);
        $selectOptions->options = [
            111 => new OptionRow(1, [], false),
        ];

        $optionValidator = new OptionsValidator(
            $quantityMaxGuesser->reveal(),
            $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->reveal(),
            $templateProductGuesser->reveal(),
            $this->dateTime,
            $orderMerger->reveal(),
            $this->cartManager->reveal()
        );
        $optionValidator->initialize($this->executionContext->reveal());
        $optionValidator->validate($selectOptions, $this->constraint->reveal());
    }

    public function testValidateQuantityMinPayableOption(): void
    {
        $this->product
            ->isAttributable()
            ->willReturn(false);

        $this->product
            ->isDeletable($this->dateTime)
            ->shouldBeCalled()
            ->willReturn(true);

        $linkedProduct = $this->prophesize(Product::class);

        $order = $this->prophesize(Order::class);
        $this->sheet
            ->hasNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn(true);
        $this->sheet
            ->getNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn([$order->reveal()]);

        $orderMerger = $this->prophesize(Merger::class);
        $orderMerger->merge([$order->reveal()])
            ->shouldBeCalled()
            ->willReturn($order->reveal());

        $order->getPlan()
            ->shouldBeCalled()
            ->willReturn($this->product->reveal());

        $includedProduct = $this->prophesize(Product\ProductIncluded::class);
        $this->product
            ->getIncludedProduct($linkedProduct->reveal())
            ->shouldBeCalled()
            ->willReturn($includedProduct->reveal());
        $includedProduct
            ->getQuantity()
            ->shouldBeCalled()
            ->willReturn(0);

        $templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);
        $templateProductGuesser
            ->guessProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn($linkedProduct->reveal());

        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $this->prophesize(
            ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode::class
        );
        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
            ->hasConflict($this->sheet->reveal(), $this->product->reveal(), 0, $order->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $constraintViolationBuilder1 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->buildViolation('package.product.quantityMinPayableOption')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->atPath('111.quantity')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->addViolation()
            ->shouldBeCalled();

        $quantityMaxGuesser = $this->prophesize(QuantityMaxGuesser::class);
        $quantityMaxGuesser
            ->getMaxByProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(0);

        $selectOptions = new SelectOptions($this->sheet->reveal(), 3);
        $selectOptions->options = [
            111 => new OptionRow(0, [], false),
        ];

        $optionValidator = new OptionsValidator(
            $quantityMaxGuesser->reveal(),
            $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->reveal(),
            $templateProductGuesser->reveal(),
            $this->dateTime,
            $orderMerger->reveal(),
            $this->cartManager->reveal()
        );
        $optionValidator->initialize($this->executionContext->reveal());
        $optionValidator->validate($selectOptions, $this->constraint->reveal());
    }

    public function testAddNotPreviouslyOrderedAndNotDeletableProduct(): void
    {
        $this->product
            ->isAttributable()
            ->willReturn(false);

        $this->product
            ->isDeletable($this->dateTime)
            ->shouldBeCalled()
            ->willReturn(false);

        $order = $this->prophesize(Order::class);
        $this->sheet
            ->hasNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn(true);
        $this->sheet
            ->getNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn([$order->reveal()]);

        $orderMerger = $this->prophesize(Merger::class);
        $orderMerger->merge([$order->reveal()])
            ->shouldBeCalled()
            ->willReturn($order->reveal());
        $order
            ->getRowForProduct($this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);
        $templateProductGuesser
            ->guessProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $this->prophesize(
            ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode::class
        );
        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
            ->hasConflict($this->sheet->reveal(), $this->product->reveal(), 1, $order->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->executionContext
            ->buildViolation('package.product.productNotDeletable')
            ->shouldNotBeCalled()
        ;

        $quantityMaxGuesser = $this->prophesize(QuantityMaxGuesser::class);
        $quantityMaxGuesser
            ->getMaxByProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(2);

        $selectOptions = new SelectOptions($this->sheet->reveal(), 3);
        $selectOptions->options = [
            111 => new OptionRow(1, [], false),
        ];

        $optionValidator = new OptionsValidator(
            $quantityMaxGuesser->reveal(),
            $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->reveal(),
            $templateProductGuesser->reveal(),
            $this->dateTime,
            $orderMerger->reveal(),
            $this->cartManager->reveal()
        );
        $optionValidator->initialize($this->executionContext->reveal());
        $optionValidator->validate($selectOptions, $this->constraint->reveal());
    }

    public function testValidateSelectedParticipantsQuantityNotMatch(): void
    {
        $this->product
            ->isAttributable()
            ->willReturn(true);

        $this->sheet
            ->hasNotCancelledOrders()
            ->shouldBeCalled()
            ->willReturn(false);

        $templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);
        $templateProductGuesser
            ->guessProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $this->prophesize(
            ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode::class
        );
        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
            ->hasConflict($this->sheet->reveal(), $this->product->reveal(), 3, null)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $constraintViolationBuilder1 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->buildViolation('package.product.selectedParticipantsQuantityNotMatch')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->atPath('111.participants')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->setParameters(['%min%' => 0, '%max%' => 2])
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1
            ->addViolation()
            ->shouldBeCalled();

        $quantityMaxGuesser = $this->prophesize(QuantityMaxGuesser::class);
        $quantityMaxGuesser
            ->getMaxByProduct($this->sheet->reveal(), $this->product->reveal())
            ->shouldBeCalled()
            ->willReturn(2);

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $selectOptions = new SelectOptions($this->sheet->reveal(), 3);
        $selectOptions->options = [
            111 => new OptionRow(3, [$participant1->reveal(), $participant2->reveal(), $participant3->reveal()], true),
        ];

        $optionValidator = new OptionsValidator(
            $quantityMaxGuesser->reveal(),
            $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->reveal(),
            $templateProductGuesser->reveal(),
            $this->dateTime,
            $this->prophesize(Merger::class)->reveal(),
            $this->cartManager->reveal()
        );
        $optionValidator->initialize($this->executionContext->reveal());
        $optionValidator->validate($selectOptions, $this->constraint->reveal());
    }
}
