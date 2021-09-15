<?php

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Application\View\Package\GroupView;
use Proximum\Vimeet\Domain\Model\Product;

class GroupViewQueryHandler
{
    /**
     * @var OptionViewQueryHandler
     */
    private $optionViewQueryHandler;

    /**
     * @param OptionViewQueryHandler $optionViewQueryHandler
     */
    public function __construct(OptionViewQueryHandler $optionViewQueryHandler)
    {
        $this->optionViewQueryHandler = $optionViewQueryHandler;
    }

    /**
     * @param GroupViewQuery $groupViewQuery
     *
     * @return GroupView
     */
    public function handle(GroupViewQuery $groupViewQuery)
    {
        $optionViewQueryHandler = $this->optionViewQueryHandler;

        return new GroupView(
            $groupViewQuery->group->getLabel($groupViewQuery->locale),
            array_map(function (Product $product) use ($optionViewQueryHandler, $groupViewQuery) {
                return $optionViewQueryHandler->handle(
                    new OptionViewQuery(
                        $groupViewQuery->sheet,
                        $product,
                        $groupViewQuery->locale
                    )
                );
            }, $groupViewQuery->group->getOptions())
        );
    }
}
