<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UserContactEvaluationViewQueryHandler
{
    use TypeAndCategoriesTrait;

    private ContactRepositoryInterface $contactRepository;
    private TypeRepositoryInterface $typeRepository;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->contactRepository = $contactRepository;
        $this->typeRepository = $typeRepository;
    }

    /**
     * @return UserContactEvaluationView[]
     */
    public function handle(UserContactEvaluationViewQuery $query): array
    {
        $contactRows = $this->contactRepository->getEvaluationsByEvent($query->event, $query->locale);

        $typeAndCategoriesTranslatedIndexedByTypeId = $this->getTypeAndCategoriesTranslatedIndexedByTypeId(
            $this->typeRepository,
            $query->event,
            $query->locale
        );

        /** @var UserContactEvaluationView[] $userContactEvaluationViews */
        $userContactEvaluationViews = [];

        /** @var UserContactEvaluationRow $contactRow */
        foreach ($contactRows as $contactRow) {
            $votingTypeAndCategories = $typeAndCategoriesTranslatedIndexedByTypeId[$contactRow->votingTypeId];
            $evaluatedTypeAndCategories = $typeAndCategoriesTranslatedIndexedByTypeId[$contactRow->evaluatedTypeId];

            $userContactEvaluationView = new UserContactEvaluationView(
                $contactRow,
                $votingTypeAndCategories->getTypeTitle(),
                implode(', ', $votingTypeAndCategories->getCategoriesTitle()),
                $evaluatedTypeAndCategories->getTypeTitle(),
                implode(', ', $evaluatedTypeAndCategories->getCategoriesTitle())
            );

            $userContactEvaluationViews[] = $userContactEvaluationView;
        }

        return $userContactEvaluationViews;
    }
}
