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
use Zend\Form\Element\Email;
use ZfcUser\Entity\UserInterface;

class EmailRegistrar {

    const USER_STATE_ACTIVE = 1;
    const USER_STATE_NON_COMPLETE = 2;

    protected $userEntityClass = 'User\Entity\User';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CodeIssuer
     */
    protected $codeIssuer;



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
            $this->em->flush();
        }

        $this->codeIssuer->issue($user->getId(), 'email', $email);

        return true;


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



} 