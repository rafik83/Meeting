<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
