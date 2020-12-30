<?php

namespace Proximum\Vimeet\Application\Leni\BadgeLink;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet;

class AvailableChecker
{
    /** @var LeniBadgeLinkParametersQueryHandler */
    private $leniBadgeLinkParametersQueryHandler;

    public function __construct(LeniBadgeLinkParametersQueryHandler $leniBadgeLinkParametersQueryHandler)
    {
        $this->leniBadgeLinkParametersQueryHandler = $leniBadgeLinkParametersQueryHandler;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        $leniBadgeLinkParametersView = $this->leniBadgeLinkParametersQueryHandler->handle(
            new LeniBadgeLinkParametersQuery($sheet->getEvent())
        );

        if (null === $leniBadgeLinkParametersView) {
            return false;
        }

        return in_array($sheet->getType()->getId(), $leniBadgeLinkParametersView->concernedTypeIds, true);
    }
}
