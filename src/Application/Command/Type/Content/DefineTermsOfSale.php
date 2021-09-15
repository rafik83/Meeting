<?php

namespace Proximum\Vimeet\Application\Command\Type\Content;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event\Content as EventContent;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Type\Content;

class DefineTermsOfSale implements Command
{
    /** @var Type */
    public $type;

    /** @var null|Content */
    public $content;

    /** @var bool */
    public $enabled;

    /** @var array */
    public $translations;

    public function __construct(Type $type, EventContent $eventContent, ?Content $content = null)
    {
        $this->content = $content;
        $this->enabled = $content !== null;
        $this->type = $type;
        $this->translations = [];

        foreach ($this->type->getEvent()->getLocales() as $locale) {
            if ($content !== null) {
                $value = $content->getValue($locale);
            } else {
                $value = $eventContent->getValue($locale);
            }

            $this->translations[$locale] = [
                'value' => $value,
            ];
        }
    }
}
