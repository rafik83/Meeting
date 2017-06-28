<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Application\View\Tip\Event\TipView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Tests\Factory\TipFactory;
use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Application\Command\Tip\Event\AffectHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AffectHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AffectHandler */
    private $handler;
    
    /** @var TipRepositoryInterface */
    private $tipRepository;
    
    /** @var TypeRepositoryInterface */
    private $typeRepository;
    
    /** @var Tip */
    private $tip;

    /** @var Type */
    private $type;

    /** @var Affect */
    private $command;
    
    public function setUp()
    {
        $event    = EventFactory::createEvent();
        $tip      = TipFactory::createTip('awesome tip');
        $type     = new Type($event);
        $typeView = new TypeView($type->getId(), $type->getTitle('fr'), $type->getDescription('fr'));

        $tipView = new TipView($tip->getId(), 'admin_title', 'fr');

        $command        = new Affect($event);
        $command->tip   = $tipView;
        $command->types = [$typeView];

        $this->tipRepository  = $this->prophesize(TipRepositoryInterface::class);
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $this->tip            = $tip;
        $this->type           = $type;
        $this->command        = $command;
        $this->handler        = new AffectHandler($this->tipRepository->reveal(), $this->typeRepository->reveal());
    }
    public function testHandle()
    {
        $this->tipRepository->getById(null)->shouldBeCalled()->willReturn($this->tip);

        $this->typeRepository->getById(null)->shouldBeCalled()->willReturn($this->type);

        $this->tipRepository->set($this->tip)->shouldBeCalled();

        $this->handler->handle($this->command);
    }

    public function testTipNotFoundException()
    {
        $this->tipRepository->getById(null)->shouldBeCalled()->willReturn(null);

        $this->expectException(TipNotFoundException::class);

        $this->typeRepository->getById(null)->shouldNotBeCalled();

        $this->tipRepository->set($this->tip)->shouldNotBeCalled();

        $this->handler->handle($this->command);
    }
}
