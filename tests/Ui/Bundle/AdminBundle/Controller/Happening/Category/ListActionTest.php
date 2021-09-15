<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening\Category;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\Category\ListAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $authorizationAccessChecker;

    /** @var ObjectProphecy */
    private $categoryRepository;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->authorizationAccessChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationAccessChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new ListAction(
            $this->authorizationAccessChecker->reveal(),
            $this->categoryRepository->reveal(),
            $this->twig->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationAccessChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->willReturn('en');
        $this->event->getAvailableLocale('en')->willReturn('fr');

        $category1 = $this->prophesize(Category::class);
        $category2 = $this->prophesize(Category::class);
        $categories = [
            $category1->reveal(),
            $category2->reveal(),
        ];
        $this->categoryRepository->findByEvent($this->event->reveal(), 'fr')->shouldBeCalled()->willReturn($categories);

        $this->twig
            ->render(ListAction::TEMPLATE, ['event' => $this->event->reveal(), 'categories' => $categories])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new ListAction(
            $this->authorizationAccessChecker->reveal(),
            $this->categoryRepository->reveal(),
            $this->twig->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }
}
