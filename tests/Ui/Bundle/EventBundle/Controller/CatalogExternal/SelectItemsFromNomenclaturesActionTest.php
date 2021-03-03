<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\CatalogExternal;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Catalog\GetNomenclaturesByTag;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\CatalogExternal\SelectItemsFromNomenclaturesAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SelectItemsFromNomenclaturesType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class SelectItemsFromNomenclaturesActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $event;

    /** @var Request */
    private $request;

    /** @var ObjectProphecy */
    private $eventDomain;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $getNomenclaturesByTag;

    /** @var SelectItemsFromNomenclaturesAction */
    private $selectItemsFromNomenclaturesAction;

    /** @var ObjectProphecy */
    private $catalogVisibilityRepository;

    public function setUp()
    {
        $this->catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepositoryInterface::class);

        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->getNomenclaturesByTag = $this->prophesize(GetNomenclaturesByTag::class);

        $this->event = $this->prophesize(Event::class);
        $this->eventDomain = $this->prophesize(EventDomain::class);
        $this->eventDomain->getEvent()->willReturn($this->event->reveal());

        $this->request = new Request();
        $this->request->setLocale('fr');

        $this->selectItemsFromNomenclaturesAction = new SelectItemsFromNomenclaturesAction(
            $this->catalogVisibilityRepository->reveal(),
            $this->twig->reveal(),
            $this->formFactory->reveal(),
            $this->getNomenclaturesByTag->reveal()
        );
    }

    public function testAccessDeniedException()
    {
        $this->expectException(AccessDeniedException::class);

        $this->catalogVisibilityRepository->getByEvent($this->event->reveal())->shouldBeCalled()->willReturn(null);

        ($this->selectItemsFromNomenclaturesAction)(
            $this->request,
            $this->eventDomain->reveal(),
            'my_tag'
        );
    }

    public function testAction()
    {
        $catalogVisibility = $this->prophesize(CatalogVisibility::class);
        $this
            ->catalogVisibilityRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($catalogVisibility->reveal())
        ;

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
            ->twig
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
            'my_tag'
        );

        $this->assertInstanceOf(Response::class, $result);
    }
}
