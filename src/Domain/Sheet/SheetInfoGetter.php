<?php

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQueryHandler;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Catalog\CanSeeOtherSheets;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SheetInfoGetter
{
    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var CardListViewQueryHandler */
    private $cardListViewQueryHandler;

    /** @var CanSeeOtherSheets */
    private $canSeeSheet;

    private NetworkingAccessChecker $networkingAccessChecker;

    /**
     * @param CanSeeSheet                     $canSeeSheet
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     * @param TemplateDataFactory             $templateDataFactory
     * @param CardListViewQueryHandler        $cardListViewQueryHandler
     */
    public function __construct(
        CanSeeSheet $canSeeSheet,
        NomenclatureRepositoryInterface $nomenclatureRepository,
        TemplateDataFactory $templateDataFactory,
        CardListViewQueryHandler $cardListViewQueryHandler,
        NetworkingAccessChecker $networkingAccessChecker
    ) {
        $this->canSeeSheet = $canSeeSheet;
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->cardListViewQueryHandler = $cardListViewQueryHandler;
        $this->networkingAccessChecker = $networkingAccessChecker;
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
        if (!$this->canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)) {
            throw new AccessDeniedException('Access Denied');
        }

        $nomenclatures = $this->nomenclatureRepository->findByEvent($event);
        $showMeetOnline = $this->networkingAccessChecker->isSheetAllowedToAccess($sheet);

        $cardListViewQuery = new CardListViewQuery($sheetToDisplay, $user, $locale, true, $showMeetOnline);
        $participants = $this->cardListViewQueryHandler->handle($cardListViewQuery);

        $registrationTemplateData = $this
            ->templateDataFactory
            ->createRegistrationFromSheet($sheetToDisplay, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
    }
}
