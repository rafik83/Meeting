<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Filter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Filter\SheetFilter;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter\SheetFilterSubmittedDataGetter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\SheetFilterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SheetFilterSubmittedDataGetterTest extends TestCase
{
    public function testSheetFilterReturnsNullHandle()
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $sheetFilter = $this->prophesize(SheetFilter::class);
        $sheetFilter->get($event->reveal())->willReturn(null);

        $form = $this->prophesize(FormInterface::class);
        $form->submit(SheetFilterType::getDefaultFilters())->shouldBeCalled();
        $form->getData()->willReturn(['whatever' => 'data']);

        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $formFactory
            ->createNamed(
                '',
                SheetFilterType::class,
                SheetFilterType::getDefaultFilters(),
                [
                    'event' => $event->reveal(),
                    'user' => $admin->reveal(),
                    'locale' => 'fr',
                    'method' => 'GET',
                    'csrf_protection' => false,
                    'required' => false,
                    'allow_extra_fields' => true,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $sheetFilterSubmittedDataGetter = new SheetFilterSubmittedDataGetter(
            $sheetFilter->reveal(),
            $formFactory->reveal()
        );
        $this->assertEquals(
            ['whatever' => 'data'],
            $sheetFilterSubmittedDataGetter->handle($event->reveal(), $admin->reveal(), 'fr')
        );
    }

    public function testSheetFilterReturnsDataHandle()
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $sheetFilter = $this->prophesize(SheetFilter::class);
        $sheetFilter->get($event->reveal())->willReturn(['whatever' => 'filter']);

        $form = $this->prophesize(FormInterface::class);
        $form->submit(['whatever' => 'filter'])->shouldBeCalled();
        $form->getData()->willReturn(['whatever' => 'data']);

        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $formFactory
            ->createNamed(
                '',
                SheetFilterType::class,
                SheetFilterType::getDefaultFilters(),
                [
                    'event' => $event->reveal(),
                    'user' => $admin->reveal(),
                    'locale' => 'fr',
                    'method' => 'GET',
                    'csrf_protection' => false,
                    'required' => false,
                    'allow_extra_fields' => true,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $sheetFilterSubmittedDataGetter = new SheetFilterSubmittedDataGetter(
            $sheetFilter->reveal(),
            $formFactory->reveal()
        );
        $this->assertEquals(
            ['whatever' => 'data'],
            $sheetFilterSubmittedDataGetter->handle($event->reveal(), $admin->reveal(), 'fr')
        );
    }
}
