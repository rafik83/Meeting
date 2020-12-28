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

    /** @var SheetTemplateManager */
    private $sheetTemplateManager;

    /** @var RegistrationTemplateManager */
    private $registrationTemplateManager;

    /** @var NomenclatureManager */
    private $nomenclatureManager;

    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetTemplateManager $sheetTemplateManager,
        RegistrationTemplateManager $registrationTemplateManager,
        NomenclatureManager $nomenclatureManager
    ) {
        $this->typeRepository = $typeRepository;
        $this->sheetTemplateManager = $sheetTemplateManager;
        $this->registrationTemplateManager = $registrationTemplateManager;
        $this->nomenclatureManager = $nomenclatureManager;
    }

    public function create(Event $event, string $title): Type
    {
        $type = new Type($event);

        $nomenclatureService = $this->nomenclatureManager->find($event, 'Offre et besoins');
        if (null === $nomenclatureService) {
            $nomenclatureService = $this->nomenclatureManager->create($event, 'Offre et besoins', [
                'ab93de01' => ['label' => ['fr' => 'Ingénierie & Bureau d\'études', 'en' => 'Engineering and Engineering consulting firm']],
                'ab93de02' => ['label' => ['fr' => 'Modélisation et calculs', 'en' => 'Modelling and calculations']],
                'ab93de05' => ['label' => ['fr' => 'Informatique', 'en' => 'Computing']],
                'ab93de06' => ['label' => ['fr' => 'Prototypage', 'en' => 'Prototyping']],
            ]);
        }
        $type->setSheetTemplate($this->sheetTemplateManager->create($event, ['services' => $nomenclatureService->getId()]));

        $defaultLocale = $event->getLocaleFallback();
        $nomenclatureTurnover = $this->nomenclatureManager->find($event, 'Chiffre d\'Affaires');
        if (null === $nomenclatureTurnover) {
            $nomenclatureTurnover = $this->nomenclatureManager->create($event, 'Chiffre d\'Affaires', [
                'turnover1' => ['label' => [$defaultLocale => 'NC.']],
                'turnover2' => ['label' => [$defaultLocale => '\< 2 M€']],
                'turnover3' => ['label' => [$defaultLocale => "2 - 10 M€"]],
                'turnover4' => ['label' => [$defaultLocale => "10 - 50 M€"]],
                'turnover5' => ['label' => [$defaultLocale => "\>50 M€"]],
            ]);
        }
        $type->setRegistrationTemplate($this->registrationTemplateManager->create($event, ['turnover' => $nomenclatureTurnover->getId()]));
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
