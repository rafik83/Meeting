<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\OMZ;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\OMZ\Export;
use Proximum\Vimeet\Application\Command\OMZ\ExportHandler;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\View\OMZ\OmzUserListView;
use Proximum\Vimeet\Application\View\OMZ\OmzUserView;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ExportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $user   = UserFactory::create('normalizer@elao.com');
        $locale = 'fr';

        // Mock
        $translator                   = $this->prophesize(TranslatorInterface::class);
        $userRepository               = $this->prophesize(UserRepositoryInterface::class);
        $groupNameResolver            = $this->prophesize(GroupNameResolver::class);
        $typeNameResolver             = $this->prophesize(TypeNameResolver::class);
        $serializer                   = $this->prophesize(SerializerAdapterInterface::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);

        $expectedPlanning = "normalizer@elao.com";
        $omzUserView = new OmzUserView(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            "normalizer@elao.com",
            null,
            null,
            $expectedPlanning
        );
        $omzUserListView = new OmzUserListView([$omzUserView]);

        $expectedNormalizedDatas = "normalizer@elao.com";

        $command = new Export($event);
        $handler = new ExportHandler(
            $userRepository->reveal(),
            $groupNameResolver->reveal(),
            $typeNameResolver->reveal(),
            $participantPlanningFormatter->reveal(),
            $serializer->reveal(),
            $translator->reveal()
        );

        $participantPlanningFormatter->preloadPlanningHandlerForEvent($event)->shouldBeCalled();

        $userRepository->findByEvent($event)->shouldBeCalled()->willReturn([$user]);

        $participantPlanningFormatter->formatPlanningFromUserAndEventWithUnallocated($user, $event, $locale)
            ->shouldBeCalled()
            ->willReturn($expectedPlanning);

        $serializer->serialize($omzUserListView, 'csv', ["charset" => "Windows-1252"])
            ->shouldBeCalled()
            ->willReturn($expectedNormalizedDatas);

        $resultNormalizedDatas = $handler->handle($command);

        $this->assertEquals($expectedNormalizedDatas, $resultNormalizedDatas);
    }
}
