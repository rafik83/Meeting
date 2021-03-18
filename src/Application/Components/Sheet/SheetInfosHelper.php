<?php

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SheetInfosHelper
{
    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /** @var CardListViewQueryHandler */
    private $cardListViewQueryHandler;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param CardListViewQueryHandler        $cardListViewQueryHandler
     * @param TemplateDataFactory             $templateDataFactory
     */
    public function __construct(
        NomenclatureRepositoryInterface $nomenclatureRepository,
        CardListViewQueryHandler $cardListViewQueryHandler,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->cardListViewQueryHandler = $cardListViewQueryHandler;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Sheet  $sheet
     * @param User   $fromUser
     * @param string $locale
     * @param bool   $editableParticipantProfile
     *
     * @return array
     */
    public function getInfos(Sheet $sheet, User $fromUser, $locale, bool $editableParticipantProfile = true)
    {
        $nomenclatures = $this->nomenclatureRepository->findByEvent($sheet->getEvent());
        $participants  = $this->cardListViewQueryHandler->handle(
            new CardListViewQuery($sheet, $fromUser, $locale, $editableParticipantProfile)
        );

        $registrationTemplateData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
    }
}
