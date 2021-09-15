<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CatalogVisibilityManager
{
    /**
     * @var CatalogVisibilityRepositoryInterface
     */
    private $catalogVisibilityRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * CatalogVisibilityManager constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param TypeRepositoryInterface              $typeRepository
     */
    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->typeRepository              = $typeRepository;
    }

    /**
     * @param Event $event
     * @param array $types
     * @param array $categories
     *
     * @return CatalogVisibility
     */
    public function create(Event $event, array $types = [], array $categories = []): CatalogVisibility
    {
        $catalogVisibility = new CatalogVisibility($event);
        $catalogVisibility->updateTypes($types);
        $catalogVisibility->updateCategories($categories);

        $this->catalogVisibilityRepository->add($catalogVisibility);

        return $catalogVisibility;
    }

    /**
     * @param CatalogVisibility $catalogVisibility
     * @param Type              $type
     */
    public function setVisibleType(CatalogVisibility $catalogVisibility, Type $type)
    {
        $catalogVisibility->setType($type);

        $this->catalogVisibilityRepository->set($catalogVisibility);
    }

    /**
     * @param CatalogVisibility $catalogVisibility
     * @param string            $registrationUrl
     */
    public function setRegistrationUrl(CatalogVisibility $catalogVisibility, string $registrationUrl)
    {
        $catalogVisibility->setRegistrationUrl($registrationUrl);

        $this->catalogVisibilityRepository->set($catalogVisibility);
    }

    /**
     * @param CatalogVisibility $catalogVisibility
     */
    public function allowAllTypesToBeVisible(CatalogVisibility $catalogVisibility)
    {
        $types = $this->typeRepository->getTypesByEvent($catalogVisibility->getEvent());

        if (count($types) > 0) {
            $catalogVisibility->updateTypes($types);
            $this->catalogVisibilityRepository->set($catalogVisibility);
        }
    }
}
