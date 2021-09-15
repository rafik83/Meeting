<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Phone\UpdatePhoneValidationStatus;
use Proximum\Vimeet\Application\Command\Sheet\Phone\UpdatePhoneValidationStatusHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationCalculator;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationStatus;

class UpdatePhoneValidationStatusHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);
        $types = [$type1->reveal(), $type2->reveal(), $type3->reveal()];
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet4 = $this->prophesize(Sheet::class);
        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
            $sheet4->reveal(),
        ];

        $sheet1->isEnabled()->willReturn(false);
        $sheet2->isEnabled()->willReturn(true);
        $sheet3->isEnabled()->willReturn(true);
        $sheet4->isEnabled()->willReturn(true);
        $sheet2->setPhoneValidationStatus(ValidationStatus::ALL_CONFIRMED)->shouldBeCalled();
        $sheet3->setPhoneValidationStatus(ValidationStatus::PARTLY_CONFIRMED)->shouldBeCalled();
        $sheet4->setPhoneValidationStatus(ValidationStatus::NONE_CONFIRMED)->shouldBeCalled();

        $typeRepository       = $this->prophesize(TypeRepositoryInterface::class);
        $sheetRepository      = $this->prophesize(SheetRepositoryInterface::class);
        $validationCalculator = $this->prophesize(ValidationCalculator::class);

        $typeRepository->getTypesByEvent($event->reveal())->shouldBeCalled()->willReturn($types);

        $validationCalculator
            ->preloadTypeThatAllowPhones($event->reveal(), $types)
            ->shouldBeCalled()
        ;

        $sheetRepository->getByTypes($types)->shouldBeCalled()->willReturn($sheets);

        $validationCalculator
            ->getValidationStatusForSheet($sheet2->reveal())
            ->shouldBeCalled()
            ->willReturn(ValidationStatus::ALL_CONFIRMED)
        ;
        $validationCalculator
            ->getValidationStatusForSheet($sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(ValidationStatus::PARTLY_CONFIRMED)
        ;
        $validationCalculator
            ->getValidationStatusForSheet($sheet4->reveal())
            ->shouldBeCalled()
            ->willReturn(ValidationStatus::NONE_CONFIRMED)
        ;

        $sheetRepository->set($sheet2->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet3->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet4->reveal())->shouldBeCalled();

        $handler = new UpdatePhoneValidationStatusHandler(
            $typeRepository->reveal(),
            $sheetRepository->reveal(),
            $validationCalculator->reveal()
        );
        $handler->handle(new UpdatePhoneValidationStatus($event->reveal()));
    }
}
