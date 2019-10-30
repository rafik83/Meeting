<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class UserContactEvaluationViewQueryHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(TypeRepositoryInterface $typeRepository, UserRepositoryInterface $userRepository)
    {
        $this->typeRepository = $typeRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(UserContactEvaluationViewQuery $query)
    {
        $types = $this->typeRepository->getTypesAndCategoriesTranslationsByEvent($query->event, $query->locale);

        $userSheetsViews = $this->userRepository->getUserSheetsViewsByEvent($query->event);
    }
}
