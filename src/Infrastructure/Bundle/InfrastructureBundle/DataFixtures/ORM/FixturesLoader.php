<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\Alice\Fixtures;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fixtures loader.
 */
class FixturesLoader extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(EntityManagerInterface $manager)
    {
        $files = [
            __DIR__ . '/User.yml',
            __DIR__ . '/Nomenclature.yml',
            __DIR__ . '/InvoicePrefix.yml',
            __DIR__ . '/Template/SheetTemplate.yml',
            __DIR__ . '/Template/RegistrationTemplate.yml',
            __DIR__ . '/RdvCarnot2016-Event.yml',
            __DIR__ . '/RdvCarnot2016-Nomenclature.yml',
            __DIR__ . '/RdvCarnot2016-Product.yml',
            __DIR__ . '/RdvCarnot2016-Template.yml',
            __DIR__ . '/RdvCarnot2016-Type.yml',
            __DIR__ . '/RdvCarnot2016-Category.yml',
            __DIR__ . '/RdvCarnot2016-Sheet.yml',
            __DIR__ . '/RdvCarnot2016-SheetGroup.yml',
            __DIR__ . '/RdvCarnot2016-Rule.yml',
            __DIR__ . '/RdvCarnot2016-Participant.yml',
            __DIR__ . '/Happening/RdvCarnot2016-Category.yml',
            __DIR__ . '/RdvCarnot2016-Schedule.yml',
            __DIR__ . '/ASDDays2016-Event.yml',
            __DIR__ . '/ASDDays2016-Nomenclature.yml',
            __DIR__ . '/ASDDays2016-Product.yml',
            __DIR__ . '/ASDDays2016-Template.yml',
            __DIR__ . '/ASDDays2016-Type.yml',
            __DIR__ . '/ASDDays2016-Sheet.yml',
            __DIR__ . '/ASDDays2016-Sheet-Many.yml',
            __DIR__ . '/ASDDays2016-Order.yml',
            __DIR__ . '/ASDDays2016-Rule.yml',
            __DIR__ . '/ASDDays2016-Notifications.yml',
            __DIR__ . '/ASDDays2016-SearchFacet.yml',
            __DIR__ . '/ASDDays2016-Happening.yml',
            __DIR__ . '/ASDDays2016-Messaging-Campaign.yml',
            __DIR__ . '/ASDDays2016-MeetingSlot.yml',
            __DIR__ . '/ASDDays2016-Spot.yml',
            __DIR__ . '/Spanish-Event.yml',
            __DIR__ . '/Spanish-Nomenclature.yml',
            __DIR__ . '/Spanish-Template.yml',
            __DIR__ . '/Spanish-Type.yml',
            __DIR__ . '/Meeting/ASDDays2016-Meeting.yml',
            __DIR__ . '/Admin.yml',
            __DIR__ . '/AdminWithType.yml',
            __DIR__ . '/ASDDays2016-Invoice.yml',
        ];

        $options = [
            'locale'    => 'fr_FR',
            'providers' => [
                $this->container->get('vimeet_infrastructure.data_fixtures_orm.provider'),
            ],
        ];

        Fixtures::load($files, $manager, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
