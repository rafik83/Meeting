<?php

namespace Proximum\Vimeet\Application\Command\Transactional\Mail;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Customize implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $transactionalMailType;

    /** @var array */
    public $data;

    /** @var Type[] */
    public $associatedTypes;

    /** @var array */
    public $translations;

    /** @var bool */
    public $enabled;

    public function __construct(
        Event $event,
        string $transactionalMailType,
        array $data,
        array $genericTranslations = []
    ) {
        $this->event = $event;
        $this->transactionalMailType = $transactionalMailType;
        $this->data = $data;
        $this->associatedTypes = [];
        $this->translations = [];
        $this->enabled = true;

        foreach ($genericTranslations as $locale => $genericTranslation) {
            $this->translations[$locale] = [
                'subject' => $genericTranslation['subject'],
                'content' => $genericTranslation['content'],
            ];
        }
    }

    public function hasNoAssociatedTypesConflict(): bool
    {
        if (!$this->data['isCustomizableByType']) {
            return true;
        }

        return !empty($this->associatedTypes);
    }
}
