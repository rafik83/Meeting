<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ShowAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /**
     * @param EngineInterface                      $engine
     * @param QueryBusInterface                    $queryBus
     * @param AuthorizationCheckerAdapterInterface $authorizationChecker
     */
    public function __construct(
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        AuthorizationCheckerAdapterInterface $authorizationChecker
    ) {
        $this->engine               = $engine;
        $this->queryBus             = $queryBus;
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

        return $this->engine->renderResponse('EventBundle:Content:terms-of-sale.html.twig',
            [
                'sheet'   => $sheet,
                'event'   => $eventDomain->getEvent(),
                'content' => $termsOfSaleView->content,
            ]);
    }
}
