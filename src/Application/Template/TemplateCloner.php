<?php

namespace Proximum\Vimeet\Application\Template;

use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\AbstractTemplate;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

abstract class TemplateCloner
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var NomenclatureCloner
     */
    private $nomenclatureCloner;

    /**
     * TemplateCloner constructor.
     *
     * @param TemplateDataFactory $templateDataFactory
     * @param NomenclatureCloner  $nomenclatureCloner
     */
    public function __construct(TemplateDataFactory $templateDataFactory, NomenclatureCloner $nomenclatureCloner)
    {
        $this->templateDataFactory = $templateDataFactory;
        $this->nomenclatureCloner  = $nomenclatureCloner;
    }

    /**
     * @param Event            $event
     * @param AbstractTemplate $template
     */
    private function cloneNomenclatures(Event $event, AbstractTemplate $template): void
    {
        $templateData = $this->templateDataFactory->createFromTemplate($template);
        $objects      = $templateData->getNomenclatureObjects();

        foreach ($objects as $object) {
            if ($object->getNomenclatureModel()->getEvent() !== $event) {
                $original = $object->getNomenclatureModel();
                $clone    = $this->nomenclatureCloner->duplicateIfNotExists($original, $event);

                $object->setNomenclature($clone);
            }
        }

        $template->setValue($templateData->getConfig());
    }

    /**
     * @param Event            $event
     * @param AbstractTemplate $template
     */
    protected function switchEvent(Event $event, AbstractTemplate $template)
    {
        // Clone nomenclature
        $this->cloneNomenclatures($event, $template);

        // Now we can set the proper event
        $template->setEvent($event);
    }
}
