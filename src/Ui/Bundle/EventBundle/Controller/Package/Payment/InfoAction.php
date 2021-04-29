<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Package\Payment;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Package\Payment\InfoViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class InfoAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        Environment $twig,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->twig = $twig;
        $this->queryBus = $queryBus;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function __invoke(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException('Access denied!');
        }

        $view = $this->queryBus->handle(new InfoViewQuery($sheet, $request->getLocale()));

        return new Response($this->twig->render('EventBundle:Sheet:paymentInfo.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'sheet'  => $sheet,
            'view' => $view,
            'locale' => $request->getLocale(),
        ]));
    }
}
