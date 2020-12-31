<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Message;

use Proximum\Vimeet\Domain\Model\Messaging\Message;

final class Update
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var Message
     */
    private $message;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->name    = $message->getName();

        foreach ($message->getEvent()->getLocales() as $locale) {
            $this->translations[$locale] = [
                'subject' => $message->getSubject($locale),
                'content' => $message->getContent($locale),
                'locale'  => $locale,
            ];
        }
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
