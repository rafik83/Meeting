<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock\BlockToArray;
use Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock\SetArrayContentToBlock;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet\ObjectsCollectionUpdateAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Template\ObjectsCollectionType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class ObjectsCollectionUpdateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $block;

    /** @var ObjectProphecy */
    private $templateData;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $setArrayContentToBlock;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $form;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $blockToArray;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectsCollectionUpdateAction */
    private $objectsCollectionUpdateAction;

    /** @var array */
    private $blockData;

    /** @var Request */
    private $request;

    /** @var EventDomain */
    private $eventDomain;

    /** @var string */
    private $key;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getId()->willReturn(1337);

        $this->request = new Request();
        $this->request->setLocale('fr');

        $this->eventDomain = new EventDomain($this->event->reveal());
        $this->key = 'whatever-object-uid';

        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet->reveal())->willReturn(true);

        $this->block = $this->prophesize(Block::class);
        $this->block->isObjectsCollection()->shouldBeCalled()->willReturn(true);;

        $this->templateData = $this->prophesize(TemplateData::class);
        $this->templateData->getBlockByUid($this->key)->shouldBeCalled()->willReturn($this->block->reveal());

        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->templateDataFactory
            ->createFromSheet($this->sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;

        $this->commandBus = $this->prophesize(CommandBusInterface::class);

        $this->blockData = [['uid1' => null, 'uid2' => null]];
        $this->blockToArray = $this->prophesize(BlockToArray::class);
        $this->blockToArray->__invoke($this->block->reveal())->shouldBeCalled()->willReturn($this->blockData);

        $this->setArrayContentToBlock = $this->prophesize(SetArrayContentToBlock::class);

        $this->router = $this->prophesize(RouterInterface::class);
        $this->form = $this->prophesize(FormInterface::class);

        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->formFactory
            ->create(
                ObjectsCollectionType::class,
                ['objectsCollection' => $this->blockData],
                [
                    'block' => $this->block->reveal(),
                    'locale' => 'fr',
                    'locales' => ['fr', 'en'],
                ]
            )
            ->shouldBeCalled()
            ->willReturn($this->form->reveal())
        ;

        $this->engine = $this->prophesize(EngineInterface::class);

        $this->objectsCollectionUpdateAction = new ObjectsCollectionUpdateAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal(),
            $this->templateDataFactory->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->blockToArray->reveal(),
            $this->setArrayContentToBlock->reveal(),
            $this->router->reveal()
        );
    }

    public function testObjectsCollectionUpdateForm()
    {
        $this->block->getLabel('fr')->shouldBeCalled()->willReturn('Ma collection d\‘objets');

        $formView = new FormView();
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $this->form->isValid()->shouldNotBeCalled();
        $this->form->createView()->shouldBeCalled()->willReturn($formView);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();

        $this->router->generate('event_sheet_default', ['sheet' => 1337])->shouldNotBeCalled();

        $this
            ->engine
            ->render(
                '@Event/Sheet/objectsCollectionUpdate.html.twig',
                [
                    'event' => $this->event->reveal(),
                    'sheet' => $this->sheet->reveal(),
                    'locale' => 'fr',
                    'key' => $this->key,
                    'form' => $formView,
                    'label' => 'Ma collection d\‘objets',
                ]
            )
            ->shouldBeCalled()
            ->willReturn('<html>...</html>')
        ;

        $response = ($this->objectsCollectionUpdateAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->key
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('<html>...</html>', $response->getContent());
    }

    public function testObjectsCollectionUpdateFormHandled()
    {
        $data = ['objectsCollection' => [['uid1' => 'text 1', 'uid2' => 'text 2']]];
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $this->form->isValid()->shouldBeCalled()->willReturn(true);
        $this->form->getData()->shouldBeCalled()->willReturn($data);

        $this
            ->router
            ->generate('event_sheet_default', ['sheet' => 1337])
            ->shouldBeCalled()
            ->willReturn('/to/sheet/1337')
        ;

        $this
            ->engine
            ->render(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this
            ->setArrayContentToBlock
            ->__invoke($this->block->reveal(), [['uid1' => 'text 1', 'uid2' => 'text 2']])
            ->shouldBeCalled()
        ;

        $this
            ->commandBus
            ->handle(new UpdateData($this->sheet->reveal(), $this->templateData->reveal()))
            ->shouldBeCalled()
        ;

        $response = ($this->objectsCollectionUpdateAction)(
            $this->request,
            $this->eventDomain,
            $this->sheet->reveal(),
            $this->key
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/to/sheet/1337', $response->getTargetUrl());
    }
}
