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
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Form\Element\Email;
use Zend\Math\Rand;
use ZfcUser\Entity\UserInterface;
use ZfcUser\Options\UserServiceOptionsInterface;

class EmailRegistrar {

    use EventManagerAwareTrait;

    const USER_STATE_ACTIVE = 1;
    const USER_STATE_CONFIRMED_NOT_COMPLETE = 2;
    const USER_STATE_NOT_CONFIRMED_NOT_COMPLETE = 3;

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


    public function createPassword() {
        return bin2hex(openssl_random_pseudo_bytes(4));
    }

    public function getPasswordHash($password)  {
        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());

        return $bcrypt->create($password);
    }

    public function newUser($email)   {

        $element = new Email('email');
        if (!$element->getEmailValidator()->isValid($email))    {
            throw new \Exception("Invalid email");
        }


        $user = $this->getRepository()->findOneBy([
            'email' => $email
        ]);

        if ($user) {
            if ($user->getState() !== self::USER_STATE_NOT_CONFIRMED_NOT_COMPLETE) {
                throw new UserAlreadyExists("User with provided email already exists");
            }
        }
        else    {
            $user = $this->factoryUser();
            $user->setEmail($email);
            $user->setState(self::USER_STATE_NOT_CONFIRMED_NOT_COMPLETE);

            $this->em->persist($user);
        }

        //generate new password
        $password = $this->createPassword();
        $passwordHash = $this->getPasswordHash($password);

        $user->setPassword($passwordHash);

        $this->em->flush();

//        $this->codeIssuer->issue($user->getId(), 'email', $email, 'default', [
//            'email' => $user->getEmail(),
//            'password' => $password
//        ]);
        $this->issueCode($user, $password);

        $this->getEventManager()->trigger('register.post', $this, [
            'user' => $user
        ]);

        return true;
    }

    public function issueCode($user, $plainPassword)    {
        return $this->codeIssuer->issue($user->getId(), 'email', $user->getEmail(), 'extended', [
            'email' => $user->getEmail(),
            'password' => $plainPassword
        ]);

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