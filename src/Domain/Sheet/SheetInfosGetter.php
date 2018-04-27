<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQueryHandler;
use Proximum\Vimeet\Domain\Catalog\SheetAccessChecker;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SheetInfosGetter
{
    /** @var SheetAccessChecker */
    private $accessChecker;

    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var CardListViewQueryHandler */
    private $cardListViewQueryHandler;

    /**
     * @param SheetAccessChecker              $accessChecker
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param TemplateDataFactory             $templateDataFactory
     * @param CardListViewQueryHandler        $cardListViewQueryHandler
     */
    public function __construct(
        SheetAccessChecker $accessChecker,
        NomenclatureRepositoryInterface $nomenclatureRepository,
        TemplateDataFactory $templateDataFactory,
        CardListViewQueryHandler $cardListViewQueryHandler
    ) {
        $this->accessChecker = $accessChecker;
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->cardListViewQueryHandler = $cardListViewQueryHandler;
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param Sheet  $sheetToDisplay
     * @param User   $user
     * @param string $locale
     *
     * @throws \Exception
     *
     * @return array
     */
    public function sheetInfos(
        Event $event,
        Sheet $sheet,
        Sheet $sheetToDisplay,
        User $user,
        string $locale
    ): array {
        if (!$this->accessChecker->checkAccess($sheet, $sheetToDisplay)) {
            throw new AccessDeniedException('Access Denied');
        }

        $nomenclatures     = $this->nomenclatureRepository->findByEvent($event);
        $cardListViewQuery = new CardListViewQuery($sheetToDisplay, $user, $locale);
        $participants      = $this->cardListViewQueryHandler->handle($cardListViewQuery);

        $registrationTemplateData = $this
            ->templateDataFactory
            ->createRegistrationFromSheet($sheetToDisplay, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
    }
}
