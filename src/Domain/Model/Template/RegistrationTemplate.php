<?php

namespace Proximum\Vimeet\Domain\Model\Template;

use DateTimeInterface;

class RegistrationTemplate extends AbstractTemplate
{
    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->event ? $this->event->getFallback() : $this->fallback;
    }

    /**
     * @param string            $title
     * @param DateTimeInterface $createdAt
     *
     * @return RegistrationTemplate
     */
    public function duplicate($title, DateTimeInterface $createdAt)
    {
        return new $this($title, $this->value, $createdAt, $this->locales);
    }

    /**
     * @param string $title
     * @param string $fallback
     *
     * @return RegistrationTemplate
     */
    public function update($title, $fallback)
    {
        if (!$this->hasLocale($fallback)) {
            throw new \InvalidArgumentException('Default locale should be in the template locales.');
        }

        $this->title    = $title;
        $this->fallback = $fallback;

        return $this;
    }
}
