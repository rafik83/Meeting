<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Command\Catalog\GetNomenclaturesByTag;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class SelectItemsFromNomenclaturesAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CatalogAccessChecker */
    private $catalogAccessChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var GetNomenclaturesByTag */
    private $getNomenclaturesByTag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CatalogAccessChecker $catalogAccessChecker,
        EngineInterface $engine,
        GetNomenclaturesByTag $getNomenclaturesByTag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->engine = $engine;
        $this->getNomenclaturesByTag = $getNomenclaturesByTag;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain,
        string $tag
    ): Response {
        $event = $eventDomain->getEvent();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$sheet->isInCatalog()
            || !$this->catalogAccessChecker->allowedToAccess($event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $nomenclatures = ($this->getNomenclaturesByTag)($event, $tag);

        return new Response(
            $this->engine->render(
                'EventBundle:Catalog/Partial:selectItemsFromNomenclatures.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'tag' => $tag,
                    'nomenclatures' => $nomenclatures,
                ]
            )
        );
    }
}
