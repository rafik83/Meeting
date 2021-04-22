<?php

namespace Proximum\Vimeet\Application\Query\CustomLink;

use Proximum\Vimeet\Domain\Model\Event\CustomLink;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;

class CustomLinkListViewQueryHandler
{
    private CustomLinkRepositoryInterface $customLinkRepository;

    public function __construct(CustomLinkRepositoryInterface $customLinkRepository)
    {
        $this->customLinkRepository = $customLinkRepository;
    }

    public function handle(CustomLinkListViewQuery $query): CustomLinkListView
    {
        $customLinks = $this->customLinkRepository->findByEvent($query->event);

        $customLinkViews = array_map(
            static fn(CustomLink $customLink) => new CustomLinkView(
                $customLink->getId(),
                $customLink->getLabel($query->locale),
                $customLink->getUrl(),
                array_map(
                    static fn(Type $type) => $type->getTitle($query->locale),
                    $customLink->getTypes()
                ),
                $customLink->getPriority()
            ),
            $customLinks
        );

        return new CustomLinkListView($customLinkViews);
    }
}
