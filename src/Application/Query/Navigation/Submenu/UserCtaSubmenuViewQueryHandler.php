<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class UserCtaSubmenuViewQueryHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(UserCtaSubmenuViewQuery $query): ?SubmenuButtonView
    {
        $customUserIdExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $query->event,
            Type::TYPE_CUSTOM_BUTTON
        );

        if (null === $customUserIdExtraParameter) {
            return null;
        }

        $parameters = json_decode($customUserIdExtraParameter->getValue(), true);

        if (isset($parameters['concerned_type_ids'])
            && !in_array($query->sheet->getType()->getId(), $parameters['concerned_type_ids'], false)
        ) {
            return null;
        }

        $needParticipantId = strpos('%participantId%', $parameters['link']) !== false;

        $participant = $query->sheet->getUserParticipant($query->user);

        if ($needParticipantId && $participant === null) {
            return null;
        }

        $placeholders = ['%userId%', '%userEmail%', '%participantId%'];
        $values = [urlencode($query->user->getId()), urlencode($query->user->getEmail()), $participant->getId()];
        $link = str_replace($placeholders, $values, $parameters['link']);

        return new SubmenuButtonView(
            Category::CUSTOM_BUTTON_ICON,
            $parameters['button-label'][$query->locale] ?? '',
            $link,
            false,
            null,
            false,
            ['target' => '_blank']
        );
    }
}
