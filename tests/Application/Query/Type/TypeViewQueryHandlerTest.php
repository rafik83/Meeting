<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Type\TypeViewQuery;
use Proximum\Vimeet\Application\Query\Type\TypeViewQueryHandler;
use Proximum\Vimeet\Application\View\Type\TypeListsView;
use Proximum\Vimeet\Application\View\Type\TypeListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(12);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);

        $registrationTemplate1 = $this->prophesize(RegistrationTemplate::class);
        $registrationTemplate2 = $this->prophesize(RegistrationTemplate::class);
        $sheetTemplate1 = $this->prophesize(SheetTemplate::class);
        $sheetTemplate2 = $this->prophesize(SheetTemplate::class);
        $package1 = $this->prophesize(Package::class);
        $package2 = $this->prophesize(Package::class);
        $paymentConditions = $this->prophesize(Type\PaymentConditions::class);
        $registrationTemplate1->getTitle()->willReturn('registration template 1');
        $registrationTemplate2->getTitle()->willReturn('registration template 2');
        $sheetTemplate1->getTitle()->willReturn('sheet template 1');
        $sheetTemplate2->getTitle()->willReturn('sheet template 2');
        $package1->getTitle()->willReturn('package 1');
        $package2->getTitle()->willReturn('package 2');

        $type1->getId()->willReturn(14);
        $type2->getId()->willReturn(17);
        $type3->getId()->willReturn(18);
        $type1->getPosition()->willReturn(3);
        $type2->getPosition()->willReturn(1);
        $type3->getPosition()->willReturn(2);
        $type1->isHidden()->willReturn(true);
        $type2->isHidden()->willReturn(false);
        $type3->isHidden()->willReturn(true);
        $type1->getTitle('fr')->willReturn('Type 1');
        $type2->getTitle('fr')->willReturn('Type 2');
        $type3->getTitle('fr')->willReturn('Type 3');
        $type1->getRegistrationTemplate()->willReturn($registrationTemplate1->reveal());
        $type2->getRegistrationTemplate()->willReturn($registrationTemplate2->reveal());
        $type3->getRegistrationTemplate()->willReturn($registrationTemplate1->reveal());
        $type1->getSheetTemplate()->willReturn($sheetTemplate2->reveal());
        $type2->getSheetTemplate()->willReturn($sheetTemplate2->reveal());
        $type3->getSheetTemplate()->willReturn($sheetTemplate1->reveal());
        $type1->getPackage()->willReturn($package2->reveal());
        $type2->getPackage()->willReturn($package1->reveal());
        $type3->getPackage()->willReturn($package2->reveal());
        $type1->getPaymentConditions()->willReturn(null);
        $type2->getPaymentConditions()->willReturn(null);
        $type3->getPaymentConditions()->willReturn($paymentConditions->reveal());

        $paginatedResult = new PaginatedResult([$type1->reveal(), $type2->reveal(), $type3->reveal()], 1, 20, 50);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->paginate(1, 20, 12, 'fr')->shouldBeCalled()->willReturn($paginatedResult);

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository
            ->hasContentByAssociatedTypes(Type\Content::TYPE_TERMS_OF_SALE, $paginatedResult->results)
            ->shouldBeCalled()
            ->willReturn([
                ['contentId' => 12, 'associatedParticipationTypeId' => 18],
            ])
        ;

        $query = new TypeViewQuery(1, $event->reveal(), 'fr');
        $handler = new TypeViewQueryHandler($typeRepository->reveal(), $contentRepository->reveal());
        $result = $handler->handle($query);

        $expected = new TypeListsView();
        $expected->types = [
            new TypeListView(14, 3, 'Type 1', true, 'registration template 1', 'sheet template 2', 'package 2', false, false),
            new TypeListView(17, 1, 'Type 2', false, 'registration template 2', 'sheet template 2', 'package 1', false, false),
            new TypeListView(18, 2, 'Type 3', true, 'registration template 1', 'sheet template 1', 'package 2', true, true),
        ];
        $expected->results = $paginatedResult;

        $this->assertEquals($expected, $result);
    }
}
