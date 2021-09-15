<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class ItemCollection extends TemplateObject implements SearchableObjectInterface, ExportableObjectInterface
{
    /**
     * @var Item[]
     */
    private $items = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($key, $type, array $config, $locale, $fallback)
    {
        parent::__construct($key, $type, $config, $locale, $fallback);

        $this->padItems();
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $data = array_merge(['items' => []], $data);

        $this->buildItems($data);
        $this->padItems();

        return parent::setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->data['items'] = array_values(array_map(function (Item $item) {
            return $item->getData();
        }, $this->getNotEmptyItems()));

        return parent::getData();
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item $item
     *
     * @return ItemCollection
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param Item $item
     *
     * @return ItemCollection
     */
    public function removeItem(Item $item)
    {
        foreach ($this->items as $key => $value) {
            if ($item === $value) {
                unset($this->items[$key]);
            }
        }

        return $this;
    }

    /**
     * Get default items count
     *
     * @return int
     */
    public function getDefault()
    {
        return $this->getOption('default');
    }

    /**
     * Pad items
     */
    private function padItems()
    {
        $default = $this->getDefault();

        if (null !== $default) {
            $pad = $default - count($this->items);
            while ($pad-- > 0) {
                $this->items[] = new Item($this, null);
            }
        }
    }

    /**
     * Build items
     *
     * @param array $data
     */
    private function buildItems(array $data)
    {
        $this->items = array_map(
            function (array $item) {
                return new Item($this, $item['title']);
            },
            array_values($data['items'])
        );
    }

    /**
     * Get not empty items
     *
     * @return array
     */
    private function getNotEmptyItems()
    {
        return array_filter(array_values($this->items), function (Item $item) {
            return !$item->isEmpty();
        });
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->getOption('max');
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableContent(?string $locale = null)
    {
        $this->buildItems($this->getData());

        return array_filter(
            array_map(
                static function (Item $item) {
                    return $item->getTitle();
                },
                $this->items
            ),
            static function ($title) {
                return null !== $title;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableFieldname($locale, $fallback)
    {
        return $this->getLabel($locale, $fallback);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        $exportableContents = array_map(function ($content) {
            return str_replace(';', ',', $content);
        }, $this->getSearchableContent());

        return implode(';', $exportableContents);
    }
}
