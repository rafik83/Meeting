<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $templateSetter = 'set' . ucfirst($update->templateName);
        $templateGetter = 'get' . ucfirst($update->templateName);

        $currentTemplate = $update->type->$templateGetter();

        $newTemplate = $this->merge($update->key, $update->getFieldTemplate(), $currentTemplate);

        $update->type->$templateSetter($newTemplate);
        $this->typeRepository->set($update->type);
    }

    /**
     * @param string $keyField
     * @param array  $newFieldTemplate
     * @param array  $template
     *
     * @return array
     */
    private function merge($keyField, $newFieldTemplate, $template)
    {
        foreach ($template as $key => $fieldsTemplate) {
            if ($keyField === $key) {
                $template[$key] = $newFieldTemplate;
                break;
            }

            if (is_array($fieldsTemplate)) {
                $template[$key] = $this->merge($keyField, $newFieldTemplate, $fieldsTemplate);
            }
        }

        return $template;
    }
}
