<?php

namespace Proximum\Vimeet\Application\Query\Transactional\Mail\Generic;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Transactional\Mail\Generic\GenericMailView;
use Proximum\Vimeet\Domain\Model\Type;

class GenericMailViewQueryHandler
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function handle(GenericMailViewQuery $query): GenericMailView
    {
        $locale = $query->locale;

        return new GenericMailView(
            $query->key,
            $this->translator->trans($query->data['subject'], [], 'mail', $query->locale),
            $query->data['isCustomizableByType'],
            array_map(function (Type $type) use ($locale) {
                return $type->getTitle($locale);
            }, $query->remainingTypes)
        );
    }
}
