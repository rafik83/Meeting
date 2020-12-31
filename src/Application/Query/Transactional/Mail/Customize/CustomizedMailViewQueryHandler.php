<?php

namespace Proximum\Vimeet\Application\Query\Transactional\Mail\Customize;

use Proximum\Vimeet\Application\View\Transactional\Mail\Customize\CustomizedMailView;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class CustomizedMailViewQueryHandler
{
    public function handle(CustomizedMailViewQuery $query): CustomizedMailView
    {
        $locale = $query->locale;
        $types = [];

        foreach ($query->message->getAssociatedParticipationTypes() as $type) {
            $types[$type->getId()] = $type->getTitle($locale);
        }

        return new CustomizedMailView(
            $query->message->getId(),
            $query->message->getType(),
            $query->message->getSubject($locale),
            Constant::TRANSACTIONAL_MAIL_LIST[$query->message->getType()]['isCustomizableByType'],
            $query->message->isEnabled(),
            $types
        );
    }
}
