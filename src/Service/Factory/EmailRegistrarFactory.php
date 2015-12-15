<?php
/**
 * Created by PhpStorm.
 * User: fliak
 * Date: 14.12.15
 * Time: 19.52
 */

namespace Soil\NonFrictionalRegistration\Service\Factory;


use Soil\NonFrictionalRegistration\Service\EmailRegistrar;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EmailRegistrarFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator)  {
        $registrar = new EmailRegistrar();
        $registrar->setEm($serviceLocator->get('entity_manager'));
        $registrar->setCodeIssuer($serviceLocator->get('SoilNonFrictionalCodeIssuerService'));

        return $registrar;
    }

} 