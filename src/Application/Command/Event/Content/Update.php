<?php

namespace Proximum\Vimeet\Application\Command\Event\Content;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event\Content;

class Update implements Command
{
    /**
     * @var Content
     */
    public $content;

    /**
     * @var array
     */
    public $translations;

    /**
     * @param Content $content
     */
    public function __construct(Content $content)
    {
        $this->content = $content;

        foreach ($this->content->getEvent()->getLocales() as $locale) {
            $this->translations[$locale]['value'] = $this->content->getValue($locale);
        }
    }
}
