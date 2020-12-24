<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Command\Catalog\GetNomenclaturesByTag;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog\SelectItemsFromNomenclaturesAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SelectItemsFromNomenclaturesType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class SelectItemsFromNomenclaturesActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $catalogAccessChecker;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $event;

    /** @var Request */
    private $request;

    /** @var ObjectProphecy */
    private $eventDomain;

    /** @var ObjectProphecy */
    private $userDomain;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $getNomenclaturesByTag;

    /** @var SelectItemsFromNomenclaturesAction */
    private $selectItemsFromNomenclaturesAction;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->catalogAccessChecker = $this->prophesize(CatalogAccessChecker::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->getNomenclaturesByTag = $this->prophesize(GetNomenclaturesByTag::class);

        $this->event = $this->prophesize(Event::class);
        $this->eventDomain = $this->prophesize(EventDomain::class);
        $this->eventDomain->getEvent()->willReturn($this->event->reveal());

        $this->request = new Request();
        $this->request->setLocale('fr');

        $this->userDomain = $this->prophesize(UserDomain::class);
        $this->sheet = $this->prophesize(Sheet::class);

        $this->selectItemsFromNomenclaturesAction = new SelectItemsFromNomenclaturesAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->engine->reveal(),
            $this->formFactory->reveal(),
            $this->getNomenclaturesByTag->reveal()
        );
    }

    public function testAccessDeniedException()
    {
        $this->expectException(AccessDeniedException::class);

        $this
            ->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->sheet->isInCatalog()->shouldBeCalled()->willReturn(true);

        $this->catalogAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(false);

        ($this->selectItemsFromNomenclaturesAction)(
            $this->request,
            $this->eventDomain->reveal(),
            $this->sheet->reveal(),
            $this->userDomain->reveal(),
            'my_tag'
        );
    }

    public function testAction()
    {
        $this
            ->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->sheet->isInCatalog()->shouldBeCalled()->willReturn(true);

        $this->catalogAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $nomenclature = $this->prophesize(Nomenclature::class);
        $this->getNomenclaturesByTag
            ->handle($this->event->reveal(), 'my_tag')
            ->shouldBeCalled()
            ->willReturn([$nomenclature->reveal()])
        ;

        $formView = $this->prophesize(FormView::class);
        $form = $this->prophesize(FormInterface::class);
        $form->createView()->willReturn($formView->reveal());
        $this
            ->formFactory
            ->create(
                SelectItemsFromNomenclaturesType::class,
                [],
                [
                    'method' => 'GET',
                    'locale' => 'fr',
                    'nomenclatures' => [$nomenclature->reveal()],
                    'label' => false,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this
            ->engine
            ->render(
                'EventBundle:Catalog/Partial:selectItemsFromNomenclatures.html.twig',
                [
                    'event' => $this->event->reveal(),
                    'form' => $formView->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn()
        ;

        $result = ($this->selectItemsFromNomenclaturesAction)(
            $this->request,
            $this->eventDomain->reveal(),
            $this->sheet->reveal(),
            $this->userDomain->reveal(),
            'my_tag'
        );

        $this->assertInstanceOf(Response::class, $result);
    }
}
