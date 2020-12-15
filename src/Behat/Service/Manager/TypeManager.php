<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeManager
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var UserManager */
    private $userManager;

    /** @var SheetTemplateManager */
    private $sheetTemplateManager;

    /** @var RegistrationTemplateManager */
    private $registrationTemplateManager;

    /** @var NomenclatureManager */
    private $nomenclatureManager;

    public function __construct(
        TypeRepositoryInterface $typeRepository,
        UserManager $userManager,
        SheetTemplateManager $sheetTemplateManager,
        RegistrationTemplateManager $registrationTemplateManager,
        NomenclatureManager $nomenclatureManager
    ) {
        $this->typeRepository = $typeRepository;
        $this->userManager = $userManager;
        $this->sheetTemplateManager = $sheetTemplateManager;
        $this->registrationTemplateManager = $registrationTemplateManager;
        $this->nomenclatureManager = $nomenclatureManager;
    }

    public function create(Event $event, string $title): Type
    {
        $type = new Type($event);
        $type->setSheetTemplate($this->sheetTemplateManager->create($event));
        $defaultLocale = $event->getLocaleFallback();
        $this->nomenclatureManager->create($event, 'Chiffre d\'Affaires', [
            'turnover1' => ['label' => [$defaultLocale => 'NC.']],
            'turnover2' => ['label' => [$defaultLocale => '\< 2 M€']],
            'turnover3' => ['label' => [$defaultLocale => "2 - 10 M€"]],
            'turnover4' => ['label' => [$defaultLocale => "10 - 50 M€"]],
            'turnover5' => ['label' => [$defaultLocale => "\>50 M€"]],
        ]);
        $type->setRegistrationTemplate($this->registrationTemplateManager->create($event));
        $package = new Package($event, 'Forfait', new \DateTime());
        $package->enable(false, false, false);
        $type->setPackage($package);
        $type->translate($defaultLocale, $title, 'Description du type '.$title);

        $this->typeRepository->add($type);

        return $type;
    }

    public function set(Type $type)
    {
        $this->typeRepository->set($type);
    }

    public function assignPackageToType(Type $type, Package $package): void
    {
        $type->setPackage($package);

        $this->typeRepository->set($type);
    }
}
