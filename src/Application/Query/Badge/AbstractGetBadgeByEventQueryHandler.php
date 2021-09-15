<?php

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

abstract class AbstractGetBadgeByEventQueryHandler
{
    /** @var QueryBusInterface */
    protected $queryBus;

    /** @var QRCodeGeneratorInterface */
    private $qrCodeGenerator;

    public function __construct(
        QueryBusInterface $queryBus,
        QRCodeGeneratorInterface $qrCodeGenerator
    ) {
        $this->queryBus = $queryBus;
        $this->qrCodeGenerator = $qrCodeGenerator;
    }

    public function handle(AbstractGetBadgeByEventQuery $query): UserBadgeByEventView
    {
        $type = $this->getType($query);

        /** @var Badge $badge */
        $badge = $this->queryBus->handle(new GetBadgeConfigurationByTypeQuery($type));

        if (!$badge->isActivated()) {
            throw new AccessToBadgeDeniedException('Badge for this type is not activated');
        }

        $userInfo = $this->getUserInfo($query);

        [$qrCodeIdentifier, $qrCodeImageBase64] = $this->getQrCodeImageBase64($query, $badge);

        $country = null;

        if ($badge->isShowCountry()) {
            $country = $this->getCountryString($query, $badge);
        }

        $sheetTitle = null;

        if ($badge->isShowSheetTitle()) {
            $sheetTitle = $this->getSheetTitle($query);
        }

        return new UserBadgeByEventView(
            $sheetTitle,
            $badge->isShowFirstName() ? $userInfo['firstName'] : null,
            $badge->isShowLastName() ? $userInfo['lastName'] : null,
            $badge->isShowPosition() ? $userInfo['position'] : null,
            $this->getTypeOrCategoryLabel($badge),
            $qrCodeIdentifier,
            $qrCodeImageBase64,
            $this->getHeader($query->event, $badge),
            $badge->getFooterTextColor(),
            $badge->getFooterColor(),
            $country,
            $badge->isMirrored(),
            $badge->getLeftImage(),
            $badge->getRightImage(),
            $badge->isRightImageFullHeight(),
            $query->event->getConfiguration()->getHeaderLeftColor(),
            $query->event->getConfiguration()->getHeaderRightColor()
        );
    }

    abstract protected function getType(AbstractGetBadgeByEventQuery $query): Type;

    abstract protected function getSheetTitle(AbstractGetBadgeByEventQuery $query): string;

    protected function getHeader(Event $event, Badge $badge): ?string
    {
        if (!$badge->isShowHeader()) {
            return null;
        }

        if (null !== $badge->getHeader()) {
            return $badge->getHeader();
        }

        return $event->getLocalizedMobileLogo($event->getFallback());
    }

    private function getTypeOrCategoryLabel(Badge $badge): ?string
    {
        if (!$badge->isShowFooterTypeOrCategory()) {
            return null;
        }

        if ($badge->isShowFooterType()) {
            return $this->getTypeString($badge);
        }

        return $this->getCategoryString($badge);
    }

    protected function getTypeString(Badge $badge): string
    {
        return $badge->getType()->getTitle($badge->getEvent()->getFallback());
    }

    abstract protected function getCategoryString(Badge $badge): ?string;

    /**
     * @param AbstractGetBadgeByEventQuery $query
     *
     * @return string[]
     */
    abstract protected function getUserInfo(AbstractGetBadgeByEventQuery $query): array;

    abstract protected function getQrCodeIdentifier(AbstractGetBadgeByEventQuery $query): string;

    abstract protected function getCountryString(AbstractGetBadgeByEventQuery $query, Badge $badge): ?string;

    /**
     * @param AbstractGetBadgeByEventQuery $query
     * @param Badge                        $badge
     *
     * @return string[]
     */
    protected function getQrCodeImageBase64(AbstractGetBadgeByEventQuery $query, Badge $badge): array
    {
        $qrCodeIdentifier = null;
        $qrCodeImageBase64 = null;

        if ($badge->isShowQRCode()) {
            $qrCodeIdentifier = $this->getQrCodeIdentifier($query);
            $qrCodeImageBase64 = $this->qrCodeGenerator->generateBase64Image($qrCodeIdentifier);
        }

        return [$qrCodeIdentifier, $qrCodeImageBase64];
    }
}
