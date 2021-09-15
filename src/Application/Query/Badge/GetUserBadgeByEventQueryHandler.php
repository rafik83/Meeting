<?php

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\User\Sheet\FirstParticipantSheetOfUserGetter;
use Proximum\Vimeet\Infrastructure\Adapter\IntlAdapter;

class GetUserBadgeByEventQueryHandler extends AbstractGetBadgeByEventQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var CategoryNameResolver */
    private $categoryNameResolver;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var UserInfoGuesser */
    private $userInfoGuesser;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var FirstParticipantSheetOfUserGetter */
    private $firstParticipantSheetOfUserGetter;

    /** @var IntlAdapter */
    private $intlAdapter;

    /** @var Sheet[] */
    private $userSheets;

    public function __construct(
        QueryBusInterface $queryBus,
        QRCodeGeneratorInterface $qrCodeGenerator,
        SheetRepositoryInterface $sheetRepository,
        GroupNameResolver $groupNameResolver,
        CategoryNameResolver $categoryNameResolver,
        TypeNameResolver $typeNameResolver,
        UserInfoGuesser $userInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser,
        FirstParticipantSheetOfUserGetter $firstParticipantSheetOfUserGetter,
        IntlAdapter $intlAdapter
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->groupNameResolver = $groupNameResolver;
        $this->categoryNameResolver = $categoryNameResolver;
        $this->typeNameResolver = $typeNameResolver;
        $this->userInfoGuesser = $userInfoGuesser;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->firstParticipantSheetOfUserGetter = $firstParticipantSheetOfUserGetter;
        $this->intlAdapter = $intlAdapter;

        parent::__construct($queryBus, $qrCodeGenerator);
    }

    public function handle(AbstractGetBadgeByEventQuery $query): UserBadgeByEventView
    {
        $this->userSheets = $this->sheetRepository->getSheetsByUserAndEvent($query->user, $query->event);

        if (empty($this->userSheets)) {
            throw new AccessToBadgeDeniedException('Badge for this user is not activated');
        }

        return parent::handle($query);
    }

    protected function getSheetTitle(AbstractGetBadgeByEventQuery $query): string
    {
        return $this->groupNameResolver->resolve($query->event, $query->user, $this->userSheets);
    }

    protected function getCategoryString(Badge $badge): ?string
    {
        $category = $this->categoryNameResolver->resolveCategoryForPreloadSheets($this->userSheets);

        if (null === $category) {
            return null;
        }

        return $category->getTitle($badge->getEvent()->getFallback());
    }

    protected function getUserInfo(AbstractGetBadgeByEventQuery $query): array
    {
        $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant(
            $query->user,
            $query->event->getFallback(),
            $this->userSheets
        );

        return $userInfo;
    }

    protected function getQrCodeIdentifier(AbstractGetBadgeByEventQuery $query): string
    {
        return $this->queryBus->handle(new QRCodeIdentifierQuery($query->event, $query->user));
    }

    protected function getCountryString(AbstractGetBadgeByEventQuery $query, Badge $badge): ?string
    {
        $country = null;
        $sheet = $this->firstParticipantSheetOfUserGetter->getFirstParticipantSheet($query->user, $this->userSheets);

        if ($sheet) {
            $sheetInfos = $this->sheetInfoGuesser->guessSheetInfos($sheet);

            if (!empty($sheetInfos[Tag::SHEET_COUNTRY])) {
                $country = $this->intlAdapter->getCountryName($sheetInfos[Tag::SHEET_COUNTRY]);
            }
        }

        return $country;
    }

    protected function getType(AbstractGetBadgeByEventQuery $query): Type
    {
        return $this->typeNameResolver->resolveTypeWithPreloadedSheets($this->userSheets);
    }
}
