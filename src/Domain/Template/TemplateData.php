<?php

namespace Proximum\Vimeet\Domain\Template;

class TemplateData extends Block
{
    public function getConfig(): array
    {
        $normalizedConfig = $this->normalize();

        return \is_array($normalizedConfig) ? $normalizedConfig : [];
    }

    /**
     * @return Block[]
     */
    public function getBlocksAsSteps(): array
    {
        $blocksAsSteps = [];
        $step = 1;

        foreach ($this->children as $column) {
            foreach ($column as $child) {
                if ($child instanceof Block) {
                    $blocksAsSteps[$step] = $child;
                    $step++;
                }
            }
        }

        return $blocksAsSteps;
    }

    public function sanitizedDataWithoutType(array $types = []): void
    {
        if (empty($types)) {
            return;
        }

        foreach ($this->getObjects() as $objectKey => $object) {
            $data = $object->getData();
            $sanitizedData = $data;

            if (\is_array($data)) {
                foreach ($data as $key => $datum) {
                    if (\in_array($key, $types, true)) {
                        unset($sanitizedData[$key]);
                    }
                }
            }

            $object->setData($sanitizedData);
        }
    }
}
