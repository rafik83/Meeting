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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
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
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateData = $this->prophesize(TemplateData::class);
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(Response::class);
        $event = $this->prophesize(Event::class);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);

        $registrationTemplate->getEvent()->willReturn($event->reveal());

        $authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $authorizationChecker
            ->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $templateDataFactory
            ->createRegistrationFromTemplate(
                $registrationTemplate->reveal(),
                'fr'
            )
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $engine
            ->renderResponse('AdminBundle:RegistrationTemplate:builder.html.twig', [
                'event' => $event->reveal(),
                'registrationTemplate' => $registrationTemplate->reveal(),
                'registrationTemplateData' => $templateData->reveal(),
                'locale' => 'fr'
            ])
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $buildAction = new BuildAction(
            $authorizationChecker->reveal(),
            $engine->reveal(),
            $templateDataFactory->reveal()
        );
        $response = $buildAction($request->reveal(), $registrationTemplate->reveal(), 'fr');

        $this->assertInstanceOf(Response::class, $response);
    }
}
