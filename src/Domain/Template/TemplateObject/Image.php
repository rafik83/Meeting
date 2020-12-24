<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\MimeType\MimeType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Image extends EditableObject implements UploadableObjectInterface, ExportableObjectInterface
{
    /** @var null|UploadedFile */
    public $file;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getData() ? $this->getData() : '';
    }

    /**
     * @param string $image
     *
     * @return Image
     */
    public function setImage($image)
    {
        $this->data['image'] = $image;

        return $this;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @return null|string
     */
    public function getImage()
    {
        return isset($this->data['image']) ? $this->data['image'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getImage() ? $this->getImage() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValueLocalize($locale = null)
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLabel()
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setImage($value);
    }

    /**
     * @return array
     */
    public static function supportedMimeType()
    {
        return MimeType::getMimeTypesByFormats([MimeType::FORMAT_IMAGE]);
    }

    public function canDisplayImage(): bool
    {
        if (!$this->getProducts()) {
            return true;
        }

        if ($this->getSheet() instanceof Sheet) {
            $package = $this->getSheet()->getPackage();

            if (!$package->isPassable()) {
                return true;
            }

            if (!$package->hasAtLeastOneProduct($this->getProducts())) {
                return true;
            }
        }

        if (null === $this->getSelectedProduct()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isExportable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableFieldname($locale, $fallback)
    {
        return $this->getLabel($locale, $fallback);
    }
}
