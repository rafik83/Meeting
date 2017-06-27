<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Catalog\External;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Application\Command\Catalog\External\ConfigureHandler;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\CatalogVisibilityRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfigureHandlerTest extends TestCase
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var Event */
    private $event;

    /** @var Configure */
    private $command;

    public function setUp()
    {
        $this->catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepository::class);
        $this->typeRepository = $this->prophesize((TypeRepositoryInterface::class));
        $this->event = EventFactory::createEvent();
        $this->command = new Configure($this->event);
    }

    public function testHandle()
    {
        $this->command->types = [
            new Type($this->event),
            new Type($this->event),
        ];

        $this->command->categories = [
            new Category($this->event),
            new Category($this->event),
        ];

        $this
            ->catalogVisibilityRepository
            ->add(new CatalogVisibility($this->event, $this->command->types, $this->command->categories))
            ->shouldBeCalled();

        $handler = new ConfigureHandler($this->catalogVisibilityRepository->reveal());
        $handler->handle($this->command);
    }
}
