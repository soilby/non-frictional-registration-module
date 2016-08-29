<?php
/**
 * Created by PhpStorm.
 * User: fliak
 * Date: 14.12.15
 * Time: 20.43
 */

namespace Soil\NonFrictionalRegistration\Service\Factory;


use \Talaka\ContactConfirmation\Factory\CodeIssuerServiceFactory as ConconCodeIssuerServiceFactory;
use Talaka\ContactConfirmationComponent\Renderer\Letter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CodeIssuerServiceFactory extends ConconCodeIssuerServiceFactory implements FactoryInterface {


    protected function getRepository()  {
        $repo = $this->getDM()->getRepository($this->persistentEntityClass);

        $classMetadata = $this->getDM()->getClassMetadata($this->persistentEntityClass);
        $classMetadata->setCollection('nf_registration');

        return $repo;

    }


    protected function getModuleConfig()  {
        $config = parent::getModuleConfig();
        if (!array_key_exists('settings', $config)) {
            $config['settings'] = [];
        }
        $config['settings']['mail_subject'] = "Добро пожаловать на Talaka!";

        $config['settings']['from_email'] = "=?UTF-8?B?" . base64_encode('Talaka Регистрация') . "?=".' <poshta@talaka.by>';
        
        return $config;
    }

    protected function factoryRenderer($for)    {
        switch ($for) {
            case 'email':
                return new Letter(
                    $this->getRenderEngine(),
                    'user/non-frictional-registration/mail-template-extended',
                    'user/non-frictional-registration/mail-template-extended'
                );

            default:
                return parent::factoryRenderer($for);
        }
    }

} 