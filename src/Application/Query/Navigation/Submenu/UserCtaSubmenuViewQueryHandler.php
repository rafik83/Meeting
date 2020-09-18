<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;

class UserCtaSubmenuViewQueryHandler
{

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;


    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(UserCtaSubmenuViewQuery $query): ?SubmenuButtonView {

//        todo {gestion des types de fiches}
//        if (false === $this->availableChecker->isSatisfiedBy($query->sheet)) {
//            return null;
//        }

        $customUserIdExtraParameter = $this->extraParameterRepository->findByEventAndType(
        $query->event,
        Type::TYPE_CUSTOM_BUTTON
        );

        if (null === $customUserIdExtraParameter) {
            return null;
        }

        $parameters = json_decode($customUserIdExtraParameter->getValue(), true);
        $placeholders = ['%userId%','%userEmail%'];
        $values = [urlencode($query->user->getId()), urlencode($query->user->getEmail())];
        $customButton = str_replace($placeholders, $values, $parameters['link']);

        return new SubmenuButtonView(
            Category::CUSTOM_BUTTON_ICON,
            $parameters['button-label'][$query->locale],
            $customButton,
            false,
            null,
            false,
            ['target' => '_blank']
        );
    }
}
