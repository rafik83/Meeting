<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Form;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\FormTemplateTranslation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Form\FillFormAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class FillFormActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $engine;

    /** @var FillFormAction */
    private $fillFormAction;

    /** @var Request */
    private $request;

    /** @var EventDomain */
    private $eventDomain;

    /** @var FormTemplate */
    private $formTemplate;

    /** @var ObjectProphecy */
    private $type;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getType()->willReturn($this->type->reveal());

        $this->participant = $this->prophesize(Participant::class);

        $this->request = new Request();
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
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->willReturn(true)
        ;

        $this->engine = $this->prophesize(EngineInterface::class);

        $this->fillFormAction = new FillFormAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal()
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
            $this->formTemplate
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
            $this->formTemplate
        );
    }

    public function test_fill_form()
    {
        $this->request->setLocale('fr');
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

        $this
            ->engine
            ->render(
                '@Event/Form/fillForm.html.twig',
                [
                    'event' => $this->event->reveal(),
                    'sheet' => $this->sheet->reveal(),
                    'formTemplateTitle' => 'Logistique',
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
            $this->formTemplate
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Fill form html', $response->getContent());
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
}
