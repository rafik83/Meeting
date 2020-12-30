<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Application\Leni\BadgeLink\AvailableChecker;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class LeniBadgeLinkSubmenuViewQueryHandler
{
    /** @var AvailableChecker */
    private $availableChecker;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var LeniBadgeLinkParametersQueryHandler */
    private $leniBadgeLinkParametersQueryHandler;

    public function __construct(
        AvailableChecker $availableChecker,
        TranslatorInterface $translator,
        ExtraDataRepositoryInterface $extraDataRepository,
        LeniBadgeLinkParametersQueryHandler $leniBadgeLinkParametersQueryHandler
    ) {
        $this->availableChecker = $availableChecker;
        $this->translator = $translator;
        $this->extraDataRepository = $extraDataRepository;
        $this->leniBadgeLinkParametersQueryHandler = $leniBadgeLinkParametersQueryHandler;
    }

    public function handle(LeniBadgeLinkSubmenuViewQuery $query): ?SubmenuButtonView
    {
        if (false === $this->availableChecker->isSatisfiedBy($query->sheet)) {
            return null;
        }

        $leniUserIdExtraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            Type::LENI_USER_ID,
            $query->user
        );

        if (null === $leniUserIdExtraData) {
            return null;
        }

        $leniBadgeLinkParametersView = $this->leniBadgeLinkParametersQueryHandler->handle(
            new LeniBadgeLinkParametersQuery($query->event)
        );

        $leniBadgeLink = sprintf($leniBadgeLinkParametersView->link, $leniUserIdExtraData->getValue());

        return new SubmenuButtonView(
            Category::LENI_BADGE_LINK_ICON,
            $this->translator->trans(Category::LENI_BADGE_LINK),
            $leniBadgeLink,
            false,
            null,
            false,
            ['target' => '_blank']
        );
    }
}
