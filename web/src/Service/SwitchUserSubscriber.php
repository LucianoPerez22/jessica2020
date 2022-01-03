<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Description of SwitchUserSubscriber
 *
 * @author Usuario
 */
class SwitchUserSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::SWITCH_USER => [
                ['checkSwitchAuthorization', 0],
            ],
        ];
    }
    public function checkSwitchAuthorization(SwitchUserEvent $switchEvent)
    {
      if($switchEvent->getTargetUser()->isSuperAdmin() && !($switchEvent->getRequest()->get('_switch_user')=='_exit'))
      {
          throw new AccessDeniedException();
      }
    }


}