<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

final class ReceiverView
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var array
     */
    private $replaces;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $email
     * @param array  $replaces An array of format [placeholder => value]
     *                         to be used for mail rendering
     * @param string $locale   User locale
     */
    public function __construct($email, array $replaces, $locale)
    {
        $this->email    = $email;
        $this->replaces = $replaces;
        $this->locale   = $locale;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array
     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
