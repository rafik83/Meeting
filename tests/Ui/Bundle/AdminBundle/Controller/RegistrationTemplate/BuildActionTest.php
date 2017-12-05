<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate\BuildAction;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BuildActionTest extends TestCase
{
    public function testInvoke()
    {
        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $engine = $this->prophesize(EngineInterface::class);
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(Response::class);
        $event = $this->prophesize(Event::class);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);

        $registrationTemplate->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $nomenclatureRepository->findByEvent($event->reveal())->shouldBeCalled()->willReturn([]);

        $authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $authorizationChecker
            ->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $engine
            ->renderResponse('AdminBundle:RegistrationTemplate:builder.html.twig', [
                'event' => $event->reveal(),
                'registrationTemplate' => $registrationTemplate->reveal(),
                'locale' => 'fr',
                'nomenclatures' => [],
                'sheetTags' => Tag::getTemplateChoiceTags(),
            ])
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $buildAction = new BuildAction(
            $authorizationChecker->reveal(),
            $engine->reveal(),
            $nomenclatureRepository->reveal()
        );
        $response = $buildAction($request->reveal(), $registrationTemplate->reveal(), 'fr');

        $this->assertInstanceOf(Response::class, $response);
    }
}
