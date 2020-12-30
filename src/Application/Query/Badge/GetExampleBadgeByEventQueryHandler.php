<?php

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Type;

class GetExampleBadgeByEventQueryHandler extends AbstractGetBadgeByEventQueryHandler
{
    protected function getSheetTitle(AbstractGetBadgeByEventQuery $query): string
    {
        return 'Hamilton Technologies';
    }

    protected function getCategoryString(Badge $badge): ?string
    {
        return 'Catégorie d\'exemple';
    }

    protected function getUserInfo(AbstractGetBadgeByEventQuery $query): array
    {
        $userInfo = [
            'firstName' => 'Margaret',
            'lastName' => 'Hamilton',
            'position' => 'Ingénieure système',
        ];

        return $userInfo;
    }

    protected function getQrCodeIdentifier(AbstractGetBadgeByEventQuery $query): string
    {
        return '123456789012';
    }

    protected function getCountryString(AbstractGetBadgeByEventQuery $query, Badge $badge): ?string
    {
        return 'Etats-Unis';
    }

    protected function getType(AbstractGetBadgeByEventQuery $query): Type
    {
        return $query->type;
    }
}
