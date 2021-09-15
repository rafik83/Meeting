<?php

namespace Proximum\Vimeet\Application\Command\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class GetNomenclaturesByTag
{
    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /** @var TaggedNomenclatureFilterRepositoryInterface */
    private $taggedNomenclatureFilterRepository;

    public function __construct(
        NomenclatureRepositoryInterface $nomenclatureRepository,
        TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository
    ) {
        $this->taggedNomenclatureFilterRepository = $taggedNomenclatureFilterRepository;
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    public function handle(Event $event, string $tag): array
    {
        $taggedNomenclatureFilter = $this->taggedNomenclatureFilterRepository->getByEventAndTag($event, $tag);

        if (!$taggedNomenclatureFilter instanceof TaggedNomenclatureFilter) {
            return [];
        }

        return $this->nomenclatureRepository->findByEventAndIds(
            $event,
            $taggedNomenclatureFilter->getNomenclaturesId()
        );
    }
}
