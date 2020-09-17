<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;

class UserCtaSubmenuViewQueryHandler
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;


    public function __construct(
        TranslatorInterface $translator,
        ExtraParameterRepositoryInterface $extraParameterRepository

    ) {
        $this->translator = $translator;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(UserCtaSubmenuViewQuery $query): ?SubmenuButtonView {

//        todo {gestion des types de fiches}
//        if (false === $this->availableChecker->isSatisfiedBy($query->sheet)) {
//            return null;
//        }

        $customUserIdExtraData = $this->extraParameterRepository->findByEventAndType(
        $query->event,
        Type::TYPE_CUSTOM_BUTTON
        );

        if (null === $customUserIdExtraData) {
            return null;
        }

        $parameters = json_decode($customUserIdExtraData->getValue(), true);
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
