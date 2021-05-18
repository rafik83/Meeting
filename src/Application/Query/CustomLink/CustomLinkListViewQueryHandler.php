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
            static function (CustomLink $customLink) use ($query) {
                $urls = [];
                foreach ($customLink->getLocalizedUrls() as $localizedUrl) {
                    $urls[$localizedUrl->getLocale()] = $localizedUrl->getUrl();
                }

                return new CustomLinkView(
                    $customLink->getId(),
                    $customLink->getLabel($query->locale),
                    $urls,
                    array_map(
                        static fn(Type $type) => $type->getTitle($query->locale),
                        $customLink->getTypes()
                    ),
                    $customLink->getPriority()
                );
            },
            $customLinks
        );

        return new CustomLinkListView($customLinkViews);
    }
}
