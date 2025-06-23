<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\IndexController;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\InstitutionDetailController;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\RealtimeController;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\ReportsController;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\TrendsController;

#[AutoconfigureTag('routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();
        $collection->addCollection($this->controllerLoader->load(IndexController::class));
        $collection->addCollection($this->controllerLoader->load(InstitutionDetailController::class));
        $collection->addCollection($this->controllerLoader->load(RealtimeController::class));
        $collection->addCollection($this->controllerLoader->load(ReportsController::class));
        $collection->addCollection($this->controllerLoader->load(TrendsController::class));
        return $collection;
    }
}
