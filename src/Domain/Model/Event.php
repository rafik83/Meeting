<?php

namespace Proximum\Vimeet\Domain\Model;

class Event
{
    private $id;

    private $domain;

    private $title;

    private $description;

    /**
     * Get title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }
}
