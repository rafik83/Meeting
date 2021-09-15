<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ShowAction
{
    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    public function __construct(
        Environment $twig,
        QueryBusInterface $queryBus,
        AuthorizationCheckerAdapterInterface $authorizationChecker
    ) {
        $this->twig = $twig;
        $this->queryBus = $queryBus;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        if (!$this->authorizationChecker->isGranted(
            SheetVoter::EDIT,
            $sheet
        )) {
            throw new AccessDeniedException();
        }

        $termsOfSaleView = $this->queryBus->handle(
            new TermsOfSaleViewQuery(
                $eventDomain->getEvent(),
                $sheet,
                $request->getLocale()
            )
        );

        return new Response($this->twig->render('EventBundle:Content:terms-of-sale.html.twig',
            [
                'sheet'   => $sheet,
                'event'   => $eventDomain->getEvent(),
                'content' => $termsOfSaleView->content,
            ]));
    }
}
