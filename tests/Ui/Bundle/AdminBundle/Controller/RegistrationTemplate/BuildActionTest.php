<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\AddLocale;
use Proximum\Vimeet\Application\Components\Sheet\Template\CompletenessCalculator;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate\BuildAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\AddLocaleType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BuildActionTest extends TestCase
{
    public function testBuildGlobalTemplate()
    {
        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $engine = $this->prophesize(EngineInterface::class);
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $completenessCalculator = $this->prophesize(CompletenessCalculator::class);
        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $addLocaleForm = $this->prophesize(FormInterface::class);
        $addLocaleFormView = $this->prophesize(FormView::class);
        $router = $this->prophesize(RouterInterface::class);
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(Response::class);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);

        $registrationTemplate->getId()->shouldBeCalled()->willReturn(1337);
        $registrationTemplate->getEvent()->shouldBeCalled()->willReturn(null);
        $nomenclatureRepository->findGlobals()->shouldBeCalled()->willReturn([]);

        $authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $authorizationChecker
            ->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $completenessCalculator->compute($registrationTemplate->reveal())->shouldBeCalled()->willReturn(['fr' => 100]);

        $router
            ->generate(
                'admin_template_registration_add_locale',
                ['template' => 1337]
            )
            ->shouldBeCalled()
            ->willReturn('/url')
        ;

        $formFactory
            ->create(
                AddLocaleType::class,
                new AddLocale($registrationTemplate->reveal()),
                [
                    'action' => '/url',
                    'submit' => true,
                    'template' => $registrationTemplate->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn($addLocaleForm->reveal())
        ;

        $addLocaleForm->createView()->shouldBeCalled()->willReturn($addLocaleFormView->reveal());

        $engine
            ->renderResponse('AdminBundle:RegistrationTemplate:builder.html.twig', [
                'addLocaleForm' => $addLocaleFormView->reveal(),
                'completeness' => ['fr' => 100],
                'event' => null,
                'locale' => 'fr',
                'nomenclatures' => [],
                'uploadFormats' => UploadObject::ALLOWED_FORMATS,
                'registrationTemplate' => $registrationTemplate->reveal(),
                 'registrationTemplateTagView' => Tag::getRegistrationTemplateTagView(),
            ])
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $buildAction = new BuildAction(
            $authorizationChecker->reveal(),
            $completenessCalculator->reveal(),
            $engine->reveal(),
            $formFactory->reveal(),
            $nomenclatureRepository->reveal(),
            $router->reveal(),
            true
        );
        $response = $buildAction($request->reveal(), $registrationTemplate->reveal(), 'fr');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testBuildEventTemplate()
    {
        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $engine = $this->prophesize(EngineInterface::class);
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $completenessCalculator = $this->prophesize(CompletenessCalculator::class);
        $formFactory = $this->prophesize(FormFactoryInterface::class);
        $router = $this->prophesize(RouterInterface::class);
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

        $completenessCalculator->compute($registrationTemplate->reveal())->shouldBeCalled()->willReturn(['fr' => 100]);

        $engine
            ->renderResponse('AdminBundle:RegistrationTemplate:builder.html.twig', [
                'addLocaleForm' => null,
                'completeness' => ['fr' => 100],
                'event' => $event->reveal(),
                'locale' => 'fr',
                'nomenclatures' => [],
                'uploadFormats' => UploadObject::ALLOWED_FORMATS,
                'registrationTemplate' => $registrationTemplate->reveal(),
                'registrationTemplateTagView' => Tag::getRegistrationTemplateTagView(),
            ])
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $buildAction = new BuildAction(
            $authorizationChecker->reveal(),
            $completenessCalculator->reveal(),
            $engine->reveal(),
            $formFactory->reveal(),
            $nomenclatureRepository->reveal(),
            $router->reveal(),
            true
        );
        $response = $buildAction($request->reveal(), $registrationTemplate->reveal(), 'fr');

        $this->assertInstanceOf(Response::class, $response);
    }
}
