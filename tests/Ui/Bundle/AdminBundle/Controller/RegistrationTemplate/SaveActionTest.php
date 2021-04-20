<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Save;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate\SaveAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SaveActionTest extends TestCase
{
    public function testSaveAction()
    {
        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $commandBus = $this->prophesize(CommandBusInterface::class);
        $request = $this->prophesize(Request::class);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);

        $authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $authorizationChecker
            ->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $registrationTemplate->hasLocale('fr')->shouldBeCalled()->willReturn(true);

        $request->getContent()->shouldBeCalled()->willReturn('{"data":"json"}');
        $commandBus
            ->handle(new Save($registrationTemplate->reveal(), ['data' => 'json']))
            ->shouldBeCalled()
        ;

        $saveAction = new SaveAction(
            $authorizationChecker->reveal(),
            $commandBus->reveal()
        );
        $response = $saveAction($request->reveal(), $registrationTemplate->reveal(), 'fr');

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testLocaleNotExists()
    {
        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $commandBus = $this->prophesize(CommandBusInterface::class);
        $request = $this->prophesize(Request::class);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);

        $authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $authorizationChecker
            ->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $registrationTemplate->hasLocale('fr')->shouldBeCalled()->willReturn(false);
        $request->getContent()->shouldNotBeCalled();

        $saveAction = new SaveAction(
            $authorizationChecker->reveal(),
            $commandBus->reveal()
        );
        $response = $saveAction($request->reveal(), $registrationTemplate->reveal(), 'fr');

        $this->assertEquals(
            ['error' => 'Locale "fr" does not exist for this template'],
            json_decode($response->getContent(), true)
        );
    }
}
