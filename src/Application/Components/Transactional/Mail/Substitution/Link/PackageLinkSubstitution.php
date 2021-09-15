<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;

class PackageLinkSubstitution implements SubstituteInterface
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(EventUrlGeneratorInterface $eventUrlGenerator)
    {
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet()) {
            return '';
        }

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            'event_package_redirect_depending_on_context',
            [
                'sheet'   => $prepareMail->sheet->getId(),
                '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
            ]
        );
    }
}
