<?php

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Application\View\FormTemplate\FormTemplateView;
use Proximum\Vimeet\Application\View\Type\TypeListsView;
use Proximum\Vimeet\Application\View\Type\TypeListView;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeViewQueryHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var ContentRepositoryInterface */
    private $contentRepository;

    public function __construct(
        TypeRepositoryInterface $typeRepository,
        ContentRepositoryInterface $contentRepository
    ) {
        $this->typeRepository = $typeRepository;
        $this->contentRepository = $contentRepository;
    }

    public function handle(TypeViewQuery $query): TypeListsView
    {
        $typeResults = $this->typeRepository->paginate(
            $query->page,
            20,
            $query->event->getId(),
            $query->locale
        );

        $contents = $this->contentRepository->hasContentByAssociatedTypes(
            Type\Content::TYPE_TERMS_OF_SALE,
            $typeResults->results
        );

        $contentIndexedByTypeId = $this->indexContentByTypeId($contents);

        $typeListsView = new TypeListsView();

        /** @var Type $type */
        foreach ($typeResults as $type) {
            $formTemplateViews = [];

            foreach ($type->getFormTemplates() as $formTemplate) {
                $formTemplateViews[] = new FormTemplateView(
                    $formTemplate->getTitle(),
                    $formTemplate->isPublished()
                );
            }

            $typeListsView->types[] = new TypeListView(
                $type->getId(),
                $type->getPosition(),
                $type->getTitle($query->locale),
                $type->isHidden(),
                (null !== $type->getRegistrationTemplate()) ? $type->getRegistrationTemplate()->getTitle() : '',
                (null !== $type->getSheetTemplate()) ? $type->getSheetTemplate()->getTitle() : '',
                $formTemplateViews,
                (null !== $type->getPackage()) ? $type->getPackage()->getTitle() : '',
                null !== $type->getPaymentConditions(),
                isset($contentIndexedByTypeId[$type->getId()]),
                $type->canMoveMeeting(),
                $type->canRemoveMeeting()
            );
        }

        $typeListsView->results = $typeResults;

        return $typeListsView;
    }

    private function indexContentByTypeId(array $contents): array
    {
        $indexedContent = [];

        foreach ($contents as $content) {
            $indexedContent[$content['associatedParticipationTypeId']] = true;
        }

        return $indexedContent;
    }
}
