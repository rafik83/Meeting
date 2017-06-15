<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Serializer;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Serializer\Normalizer\EventUserSchedulesNormalizer;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\View\Normalizer\EventUserSchedulesNormalizerView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

/**
 * Called EventUserSchedulesNormalizer but not really testing the Symfony normalizer
 * We test only the treatment made by the EventUserSchedulesNormalizer
 */
class EventUserSchedulesNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create('normalizer@elo.com');

        $eventUserSchedulesNormalizerView = new EventUserSchedulesNormalizerView($event);

        $expectedUserRawData = [
            "participantId" => null,
            "companyName" => null,
            "description" => null,
            "type" => null,
            "title" => null,
            "firstName" => null,
            "lastName" => null,
            "position" => null,
            "phonePrefix" => null,
            "phoneNumber" => null,
            "email" => "normalizer@elo.com",
            "mobilePhonePrefix" => null,
            "mobilePhone" => null,
            "planning" => null,
        ];

        $expectedView = [
                0 => $expectedUserRawData,
        ];

        // Mock
        $translator                   = $this->prophesize(TranslatorInterface::class);
        $userRepository               = $this->prophesize(UserRepositoryInterface::class);
        $groupNameResolver            = $this->prophesize(GroupNameResolver::class);
        $typeNameResolver             = $this->prophesize(TypeNameResolver::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);

        $normalizer = new EventUserSchedulesNormalizer(
            $translator->reveal(),
            $userRepository->reveal(),
            $groupNameResolver->reveal(),
            $typeNameResolver->reveal(),
            $participantPlanningFormatter->reveal()
        );

        $userRepository->findByEvent($event)->shouldBeCalled()->willReturn([$user]);

        $resultView = $normalizer->normalize($eventUserSchedulesNormalizerView);

        $this->assertEquals($resultView, $expectedView);
    }
}
