<?php

namespace Proximum\Vimeet\Application\Command\Type\Badge;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ConfigureHandler
{
    /** @var BadgeRepositoryInterface */
    private $badgeRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(
        BadgeRepositoryInterface $badgeRepository,
        FileStorageInterface $fileStorage
    ) {
        $this->badgeRepository = $badgeRepository;
        $this->fileStorage = $fileStorage;
    }

    public function handle(Configure $configure): void
    {
        if ($configure->removeLeftImage && $configure->leftImage instanceof UploadedFile) {
            throw new RemovingWhileAddingLeftImageException();
        }

        if ($configure->removeRightImage && $configure->rightImage instanceof UploadedFile) {
            throw new RemovingWhileAddingRightImageException();
        }

        if ($configure->isMirrored && $configure->isRightImageFullHeight) {
            throw new MirroringAndFullHeightImageIncompatibilityException();
        }

        $badge = $configure->badge;

        if (!$badge instanceof Badge) {
            $badge = new Badge($configure->event, $configure->type);
        }

        if ($configure->removeLeftImage && null === $badge->getLeftImage()) {
            throw new NoLeftImageToRemoveException();
        }

        if ($configure->removeRightImage && null === $badge->getRightImage()) {
            throw new NoRightImageToRemoveException();
        }

        if ($configure->isRightImageFullHeight
            && !$configure->rightImage instanceof UploadedFile
            && null === $badge->getRightImage()) {
            throw new NoRightImageToSetFullHeightException();
        }

        // save header, remove previous
        $header = $previousHeader = $badge->getHeader();

        if ($configure->header instanceof UploadedFile) {
            $header = $this->fileStorage->upload($configure->header);

            if (null !== $previousHeader) {
                $this->fileStorage->remove($previousHeader);
            }
        }

        // save left image, remove previous
        $leftImage = $previousLeftImage = $badge->getLeftImage();

        if ($configure->leftImage instanceof UploadedFile) {
            $leftImage = $this->fileStorage->upload($configure->leftImage);

            if (null !== $previousLeftImage) {
                $this->fileStorage->remove($previousLeftImage);
            }
        }

        // save right image, remove previous
        $rightImage = $previousRightImage = $badge->getRightImage();

        if ($configure->rightImage instanceof UploadedFile) {
            $rightImage = $this->fileStorage->upload($configure->rightImage);

            if (null !== $previousRightImage) {
                $this->fileStorage->remove($previousRightImage);
            }
        }

        // remove left image
        if ($configure->removeLeftImage) {
            $leftImage = null;
            $this->fileStorage->remove($badge->getLeftImage());
        }

        // remove right image
        if ($configure->removeRightImage) {
            $rightImage = null;
            $this->fileStorage->remove($badge->getRightImage());
        }

        // set data
        $badge->update(
            $header,
            $configure->showHeader,
            $configure->showFooterTypeOrCategory,
            $configure->footerTextColor,
            $configure->footerColor,
            $configure->showPosition,
            $configure->showFirstName,
            $configure->showLastName,
            $configure->showSheetTitle,
            $configure->showQRCode,
            $configure->activated,
            $configure->conditioned,
            $configure->conditionedByPackage,
            $configure->showCountry,
            $configure->conditionedByStates,
            $configure->isMirrored,
            $leftImage,
            $rightImage,
            $configure->isRightImageFullHeight
        );

        if ($configure->badge instanceof Badge) {
            $this->badgeRepository->set($badge);

            return;
        }

        $this->badgeRepository->add($badge);
    }
}
