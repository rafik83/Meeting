<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Form;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Template\Form\FillStepCommand;
use Proximum\Vimeet\Application\Query\Participant\Sheet\ParticipantListViewQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FillStepQuery;
use Proximum\Vimeet\Application\View\Participant\Sheet\ParticipantListView;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Application\View\Template\Form\BreadCrumbView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\FormTemplateTranslation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\BlockForGivenStepNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\GivenStepIsRequiredAndNotFilledException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Form\FillFormAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\Form\FillStepType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class FillFormActionTest extends TestCase
{
    /** @var FillFormAction */
    private $fillFormAction;

    /** @var Request */
    private $request;

    /** @var EventDomain */
    private $eventDomain;

    /** @var FormTemplate */
    private $formTemplate;

    /** @var ObjectProphecy */
    private $type,
        $event,
        $participant,
        $sheet,
        $authorizationChecker,
        $twig,
        $queryBus,
        $router,
        $formFactory,
        $commandBus,
        $flashBag
    ;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getType()->willReturn($this->type->reveal());

        $this->participant = $this->prophesize(Participant::class);

        $this->request = new Request();
        $this->request->setLocale('en');
        $this->eventDomain = new EventDomain($this->event->reveal());
        $this->formTemplate = new FormTemplate(
            $this->event->reveal(),
            'Form Logistic',
            [],
            ['fr', 'en'],
            'fr',
            new \DateTime()
        );

        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this
            ->authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(true)
        ;
        $this
            ->authorizationChecker
            ->isGranted('ROLE_PREVIOUS_ADMIN')
            ->willReturn(false)
        ;
        $this
            ->authorizationChecker
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;
        $this->sheet->hasParticipant($this->participant->reveal())->willReturn(true);

        $this->twig = $this->prophesize(Environment::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);

        $this->fillFormAction = new FillFormAction(
            $this->authorizationChecker->reveal(),
            $this->twig->reveal(),
            $this->queryBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal()
        );
    }

    public function test_form_template_not_published()
    {
        $this->expectException(AccessDeniedException::class);

        ($this->fillFormAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->formTemplate,
            1
        );
    }

    public function test_form_template_has_not_sheet_type()
    {
        $this->expectException(AccessDeniedException::class);

        $this->publish($this->formTemplate);

        $anotherType = $this->prophesize(Type::class);
        $this->setTypes($this->formTemplate, new ArrayCollection([$anotherType->reveal()]));

        ($this->fillFormAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->formTemplate,
            1
        );
    }

    public function test_form_template_has_not_given_step()
    {
        $this->publish($this->formTemplate);
        $this->setTypes($this->formTemplate, new ArrayCollection([$this->type->reveal()]));

        $this->queryBus
            ->handle(new FillStepQuery(
                $this->formTemplate,
                $this->sheet->reveal(),
                $this->participant->reveal(),
                'en',
                9
            ))
            ->shouldBeCalled()
            ->willThrow(BlockForGivenStepNotFoundException::class)
        ;

        $this->router->generate('event')->shouldBeCalled()->willReturn('/home');

        $result = ($this->fillFormAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->formTemplate,
            9
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function test_form_template_has_previous_step_not_filled()
    {
        $this->publish($this->formTemplate);
        $this->setTypes($this->formTemplate, new ArrayCollection([$this->type->reveal()]));

        $exception = new GivenStepIsRequiredAndNotFilledException(2);
        $this->queryBus
            ->handle(new FillStepQuery(
                $this->formTemplate,
                $this->sheet->reveal(),
                $this->participant->reveal(),
                'en',
                3
            ))
            ->shouldBeCalled()
            ->willThrow($exception)
        ;

        $this->sheet->getId()->shouldBeCalled()->willReturn(14);
        $this->participant->getId()->shouldBeCalled()->willReturn(16);
        $this->setId($this->formTemplate, 12);
        $this->router
            ->generate('event_participant_fill_form', [
                'formTemplate' =>12,
                'sheet' => 14,
                'participant' => 16,
                'step' => 2
            ])
            ->shouldBeCalled()
            ->willReturn('/redirect-to-previous-step/2')
        ;

        $result = ($this->fillFormAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->formTemplate,
            3
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function test_fill_form()
    {
        $this->publish($this->formTemplate);
        $this->setTypes($this->formTemplate, new ArrayCollection([$this->type->reveal()]));
        $this->setTranslations(
            $this->formTemplate,
            new ArrayCollection(
                [
                    'en' => new FormTemplateTranslation($this->formTemplate, 'en', 'Logistic'),
                    'fr' => new FormTemplateTranslation($this->formTemplate, 'fr', 'Logistique'),
                ]
            )
        );

        $this->event->getCountry()->willReturn('FR');
        $this->event->getLocales()->willReturn(['en', 'fr']);
        $block = $this->prophesize(Block::class);
        $breadCrumb = $this->prophesize(BreadCrumbView::class);
        $blockStepView = new BlockStepView($block->reveal(), 'description', $breadCrumb->reveal());

        $this->queryBus
            ->handle(new FillStepQuery(
                $this->formTemplate,
                $this->sheet->reveal(),
                $this->participant->reveal(),
                'en',
                1
            ))
            ->shouldBeCalled()
            ->willReturn($blockStepView)
        ;

        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());
        $this->formFactory
            ->create(FillStepType::class, $block, [
                'blockStepView' => $blockStepView,
                'country' => 'FR',
                'locale' => 'en',
                'locales' => ['en', 'fr'],
                'isAdmin' => false,
            ])->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $participantList = $this->prophesize(ParticipantListView::class);
        $this->queryBus
            ->handle(new ParticipantListViewQuery($this->sheet->reveal(), $this->participant->reveal(), 'en'))
            ->shouldBeCalled()
            ->willReturn($participantList)
        ;
        $this
            ->twig
            ->render(
                '@Event/Form/fillForm.html.twig',
                [
                    'event' => $this->event->reveal(),
                    'sheet' => $this->sheet->reveal(),
                    'participantList' => $participantList,
                    'formTemplate' => $this->formTemplate,
                    'blockStepView' => $blockStepView,
                    'formTemplateTitle' => 'Logistic',
                    'form' => $formView->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn('Fill form html')
        ;

        $response = ($this->fillFormAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->formTemplate,
            1
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Fill form html', $response->getContent());
    }

    public function test_handle_form()
    {
        $this->publish($this->formTemplate);
        $this->setTypes($this->formTemplate, new ArrayCollection([$this->type->reveal()]));
        $this->setId($this->formTemplate, 14);
        $this->setTranslations(
            $this->formTemplate,
            new ArrayCollection(
                [
                    'en' => new FormTemplateTranslation($this->formTemplate, 'en', 'Logistic'),
                    'fr' => new FormTemplateTranslation($this->formTemplate, 'fr', 'Logistique'),
                ]
            )
        );

        $this->event->getCountry()->willReturn('FR');
        $this->event->getLocales()->willReturn(['en', 'fr']);
        $block = $this->prophesize(Block::class);
        $breadcrumb = new BreadCrumbView([], 1);
        $blockStepView = new BlockStepView($block->reveal(), 'description', $breadcrumb);

        $this->queryBus
            ->handle(new FillStepQuery(
                $this->formTemplate,
                $this->sheet->reveal(),
                $this->participant->reveal(),
                'en',
                1
            ))
            ->shouldBeCalled()
            ->willReturn($blockStepView)
        ;

        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(FillStepType::class, $block, [
                'blockStepView' => $blockStepView,
                'country' => 'FR',
                'locale' => 'en',
                'locales' => ['en', 'fr'],
                'isAdmin' => false,
            ])->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $block->getData()->shouldBeCalled()->willReturn(['toto' => ['text' => 'titi']]);
        $block->getUploadAndImageObjects()->shouldBeCalled()->willReturn([]);
        $user = $this->prophesize(User::class);
        $this->participant->getUser()->shouldBeCalled()->willReturn($user->reveal());
        $this->sheet->getId()->shouldBeCalled()->willReturn(12);
        $this->participant->getId()->shouldBeCalled()->willReturn(13);

        $command = new FillStepCommand(
            $this->formTemplate,
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $blockStepView
        );
        $this->commandBus->handle($command)->shouldBeCalled();

        $this->router
            ->generate('event_participant_fill_form',
                [
                     'sheet' => 12,
                     'participant' => 13,
                     'formTemplate' => 14,
                     'step' => 2
                ]
            )
            ->shouldBeCalled()
            ->willReturn('/next-step')
        ;

        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $response = ($this->fillFormAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->formTemplate,
            1
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    private function publish(FormTemplate $formTemplate): void
    {
        $reflection = new \ReflectionClass(FormTemplate::class);
        $formTemplatePublishedProperty = $reflection->getProperty('published');
        $formTemplatePublishedProperty->setAccessible(true);
        $formTemplatePublishedProperty->setValue($formTemplate, true);
    }

    private function setTranslations(FormTemplate $formTemplate, ArrayCollection $translations): void
    {
        $reflection = new \ReflectionClass(FormTemplate::class);
        $formTemplateTranslationsProperty = $reflection->getProperty('translations');
        $formTemplateTranslationsProperty->setAccessible(true);
        $formTemplateTranslationsProperty->setValue($formTemplate, $translations);
    }

    private function setTypes(FormTemplate $formTemplate, ArrayCollection $types): void
    {
        $reflection = new \ReflectionClass(FormTemplate::class);
        $formTemplateTypesProperty = $reflection->getProperty('types');
        $formTemplateTypesProperty->setAccessible(true);
        $formTemplateTypesProperty->setValue($formTemplate, $types);
    }

    private function setId(FormTemplate $formTemplate, int $id): void
    {
        $reflection = new \ReflectionClass(FormTemplate::class);
        $formTemplateTypesProperty = $reflection->getProperty('id');
        $formTemplateTypesProperty->setAccessible(true);
        $formTemplateTypesProperty->setValue($formTemplate, $id);
    }
}
