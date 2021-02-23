<?php

namespace Proximum\Vimeet;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__.'/../..');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', \PHP_VERSION_ID < 70400 || $this->debug);
        $container->setParameter('container.dumper.inline_factories', true);
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');

        // load legacy sf3 services
        $confDirLegacy = $this->getProjectDir().'/app/config/';
        $loader->load($confDirLegacy.'/{services}/*'.self::CONFIG_EXTS, 'glob');

        foreach (['admin', 'application', 'domain', 'event', 'infrastructure', 'third_party'] as $subDir) {
            $loader->load($confDirLegacy.'/{services}/'.$subDir.'/*'.self::CONFIG_EXTS, 'glob');
        }
        $confInfrastructureLegacy = $this->getProjectDir().'/src/Infrastructure/Bundle/InfrastructureBundle/Resources/config/*'.self::CONFIG_EXTS;
        $loader->load($confInfrastructureLegacy, 'glob');
        $confAdminLegacy = $this->getProjectDir().'/src/Ui/Bundle/AdminBundle/Resources/config/services.yml';
        $loader->load($confAdminLegacy);
        $confEventLegacy = $this->getProjectDir().'/src/Ui/Bundle/EventBundle/Resources/config/services.yml';
        $loader->load($confEventLegacy);

        // env services will overide all others
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/'.$this->environment.'/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }
}
