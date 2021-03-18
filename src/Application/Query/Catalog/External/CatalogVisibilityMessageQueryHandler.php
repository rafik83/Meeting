<?php

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Application\View\CatalogVisibility\MessageView;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityMessageQueryHandler
{
    /**
     * @var CatalogVisibilityRepositoryInterface
     */
    private $catalogVisibilityRepository;

    /**
     * CatalogVisibilityMessageQueryHandler constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     */
    public function __construct(CatalogVisibilityRepositoryInterface $catalogVisibilityRepository)
    {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
    }

    /**
     * @param CatalogVisibilityMessageQuery $query
     *
     * @return null|MessageView
     */
    public function handle(CatalogVisibilityMessageQuery $query): ?MessageView
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($query->event);

        if (null === $catalogVisibility || false === $catalogVisibility->hasMessage()) {
            return null;
        }

        $message = $catalogVisibility->getMessage($query->locale);

        if (null === $message) {
            return null;
        }

        return new MessageView($message->getTitle(), $message->getContent());
    }
}
