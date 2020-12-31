<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;

class PositionViewQueryHandler
{
    /** @var TaggedNomenclatureFilterGetter */
    private $taggedNomenclatureFilterGetter;

    /**
     * @param TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter
     */
    public function __construct(TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter)
    {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
    }

    /**
     * @param PositionViewQuery $query
     *
     * @return PositionView[]
     */
    public function handle(PositionViewQuery $query): array
    {
        $nomenclaturesItemsViews = $this->taggedNomenclatureFilterGetter->getLastNomenclaturesItems(
            $query->event,
            Tag::PARTICIPANT_POSITION,
            $query->locale
        );

        $positionViews = [];

        foreach ($nomenclaturesItemsViews->nomenclaturesItems as $key => $title) {
            $positionViews[] = new PositionView($key, $title);
        }

        usort($positionViews, function (PositionView $first, PositionView $second) {
            return strcmp($first->getTitle(), $second->getTitle());
        });

        return $positionViews;
    }
}
