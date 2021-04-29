<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Package\ProForma;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Order\ProFormaQuery;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ShowAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, EventDomain $eventDomain, Sheet $sheet, Order $order): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        if ($order->getSheet() !== $sheet || !$sheet->getPackage()->isPassable()) {
            throw new NotFoundHttpException('This page is not accessible by this user');
        }

        $view = $this->queryBus->handle(
            new ProFormaQuery(
                $sheet,
                $order,
                $request->getLocale()
            )
        );

        return new Response($this->twig->render('EventBundle:Order:pro_forma.html.twig', [
            'event' => $eventDomain->getEvent(),
            'pro_forma' => $view,
            'sheet' => $sheet,
            'order' => $order,
        ]));
    }
}
