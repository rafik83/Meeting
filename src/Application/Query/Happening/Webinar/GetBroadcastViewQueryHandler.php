<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;

class GetBroadcastViewQueryHandler
{
    /** @var HappeningBroadcastRepositoryInterface */
    public $happeningBroadcastRepository;

    public function __construct(HappeningBroadcastRepositoryInterface $happeningBroadcastRepository) {
        $this->happeningBroadcastRepository = $happeningBroadcastRepository;
    }

    public function handle(GetBroadcastViewQuery $query): ?string
    {
        $happeningBroadcast = $this->happeningBroadcastRepository->getByHappening($query->happening);
        if (null === $happeningBroadcast) {
            return null;
        }

        return $happeningBroadcast->getHlsUrl();
    }
}
