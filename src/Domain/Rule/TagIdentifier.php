<?php

namespace Proximum\Vimeet\Domain\Rule;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Rule\Exception\NotImplementedException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class TagIdentifier
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param WhoInterface $who
     *
     * @throws NotImplementedException
     *
     * @return array
     */
    public function identify(WhoInterface $who)
    {
        if ($who instanceof Category) {
            $templates = [];
            $tags = [];

            /*
             * @var Type
             */
            foreach ($who->getTypes() as $type) {
                $template = $type->getRegistrationTemplate();

                if (!isset($templates[$template->getId()])) {
                    $templates[$template->getId()] = true;
                    $tags = array_unique(array_merge($tags, $this->extractTags($template)));
                }
            }

            return $tags;
        } elseif ($who instanceof Type) {
            return $this->extractTags($who->getRegistrationTemplate());
        }

        throw new NotImplementedException(sprintf('No method for %s', \get_class($who)));
    }

    /**
     * @param RegistrationTemplate $registrationTemplate
     *
     * @return array
     */
    private function extractTags(RegistrationTemplate $registrationTemplate)
    {
        $tags = [];

        $template = $this->templateDataFactory->createFromTemplate($registrationTemplate, []);

        /** @var TemplateObject $object */
        foreach ($template->getObjects() as $object) {
            $tags = array_unique(array_merge($tags, $object->getTags()));
        }

        return array_filter($tags, function ($tag) {
            return Tag::SHEET_DATA !== $tag && Tag::PARTICIPANT_DATA !== $tag;
        });
    }
}
