<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedCatalogSheetPreviewViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\OrderByType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $event = $eventDomain->getEvent();

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event)) {
            throw $this->createNotFoundException();
        }

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $request->getLocale());

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $orderBy = ['orderBy' => Sheet\Constant::ORDER_BY_ALPHABETICAL];

        $orderByForm = $this->createForm(
            OrderByType::class,
            $orderBy,
            ['action' => $this->generateUrl('event_catalog_index')]
        );
        $ordered     = $orderByForm->handleRequest($request) && $orderByForm->isValid();

        if ($ordered) {
            $orderBy = $orderByForm->getData();
        }

        try {
            $query = new PaginatedCatalogSheetPreviewViewQuery(
                $event,
                [],
                [$orderBy['orderBy']],
                $request->query->getInt('page', 1),
                100,
                $request->getLocale(),
                $sheet
            );
            $paginatedResult = $this->get('tactician.commandbus.query')->handle($query);
        } catch (UnavailableCurrentPageException $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('EventBundle:Catalog:list.html.twig', [
                'paginatedResult' => $paginatedResult,
            ]);
        }

        return $this->render('EventBundle:Catalog:index.html.twig', [
            'event'           => $event,
            'sheet'           => $sheet,
            'isCatalog'       => true,
            'paginatedResult' => $paginatedResult,
            'orderByForm'     => $orderByForm->createView(),
        ]);
    }

    /**
     * Display a sheet.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function sheetAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $event = $eventDomain->getEvent();

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event)) {
            throw $this->createNotFoundException();
        }

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $locale = $request->getLocale();

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        $userSheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $request->getLocale());

        $rules = $this
            ->get('repository.rule_repository')
            ->getBySeerTypeAndSeeableType($userSheet->getType(), $sheet->getType())
        ;
        $ruleApplyer = $this->get('domain.rule.applyer');
        $ruleApplyer->applyRuleForTemplate($templateData, $rules);
        $ruleApplyer->applyRuleForCardList($participants, $rules);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'sheet'         => $sheet,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
            'isCatalog'     => true,
            'userSheet'     => $userSheet,
        ]);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    private function sheetInfos(Event $event, Sheet $sheet, $locale)
    {
        $nomenclatures     = $this->get('repository.nomenclature_repository')->findByEvent($event);
        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
    }

}
