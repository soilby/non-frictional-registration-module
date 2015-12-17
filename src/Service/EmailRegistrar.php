<?php
/**
 * Created by PhpStorm.
 * User: fliak
 * Date: 14.12.15
 * Time: 19.08
 */

namespace Soil\NonFrictionalRegistration\Service;

use Doctrine\ORM\EntityManager;
use Soil\NonFrictionalRegistration\Service\Exception\UserAlreadyExists;
use Talaka\ContactConfirmationComponent\Service\CodeIssuer;
use Talaka\ContactConfirmationComponent\Service\Confirmation;
use Zend\Crypt\Password\Bcrypt;
use Zend\Form\Element\Email;
use Zend\Math\Rand;
use ZfcUser\Entity\UserInterface;
use ZfcUser\Options\UserServiceOptionsInterface;

class EmailRegistrar {

    const USER_STATE_ACTIVE = 1;
    const USER_STATE_NON_COMPLETE = 2;

    protected $userEntityClass = 'User\Entity\User';

    protected $zfcUserOptions;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CodeIssuer
     */
    protected $codeIssuer;

    /**
     * @var Confirmation
     */
    protected $codeConfirmationService;



    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()  {
        return $this->em->getRepository($this->userEntityClass);
    }

    /**
     * @return UserInterface
     */
    protected function factoryUser()    {
        return new $this->userEntityClass;
    }

    public function newUser($email)   {

        $element = new Email('email');
        $isValid = $element->getEmailValidator()->isValid($email);

        $user = $this->getRepository()->findOneBy([
            'email' => $email
        ]);

        if ($user) {
            if ($user->getState() !== self::USER_STATE_NON_COMPLETE) {
                throw new UserAlreadyExists("User with provided email already exists");
            }
        }
        else    {
            $user = $this->factoryUser();
            $user->setEmail($email);
            $user->setState(self::USER_STATE_NON_COMPLETE);

            $this->em->persist($user);
        }

        //generate new password
        $password = bin2hex(openssl_random_pseudo_bytes(4));

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());
        $user->setPassword($bcrypt->create($password));

        $this->em->flush();

        $this->codeIssuer->issue($user->getId(), 'email', $email, 'default', [
            'email' => $user->getEmail(),
            'password' => $password
        ]);

        return true;


    }

    public function confirmRequest($hash)    {
        $entry = $this->codeConfirmationService->confirm($hash, 'email');



        return $entry;
    }


    /**
     * @param mixed $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * @param CodeIssuer $codeIssuer
     */
    public function setCodeIssuer($codeIssuer)
    {
        $this->codeIssuer = $codeIssuer;
    }

    /**
     * @param mixed $codeConfirmationService
     */
    public function setCodeConfirmationService($codeConfirmationService)
    {
        $this->codeConfirmationService = $codeConfirmationService;
    }



    /**
     * @return mixed
     */
    public function getZfcUserOptions()
    {
        return $this->zfcUserOptions;
    }

    /**
     * @param mixed $zfcUserOptions
     */
    public function setZfcUserOptions($zfcUserOptions)
    {
        $this->zfcUserOptions = $zfcUserOptions;
    }




} 