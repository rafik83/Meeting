<?php

namespace Proximum\Vimeet\Application\Command\Transactional\Mail;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Messaging\MessageTranslation;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\Type;

class UpdateCustomized implements Command
{
    /** @var array */
    public $data;

    /** @var Type[] */
    public $associatedTypes;

    /** @var array */
    public $translations;

    /** @var Message */
    public $message;

    /** @var bool */
    public $enabled;

    public function __construct(Message $message, array $data)
    {
        $this->data = $data;
        $this->associatedTypes = $message->getAssociatedParticipationTypes();

        $this->translations = [];
        $this->enabled = $message->isEnabled();

        foreach ($message->getEvent()->getLocales() as $locale) {
            $this->translations[$locale] = [
                'subject' => '',
                'content' => '',
            ];
        }

        /** @var MessageTranslation $translation */
        foreach ($message->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'subject' => $translation->getSubject(),
                'content' => $translation->getContent(),
            ];
        }

        $this->message = $message;
    }

    public function hasNoAssociatedTypesConflict(): bool
    {
        if (!$this->data['isCustomizableByType']) {
            return true;
        }

        return !empty($this->associatedTypes);
    }
}
