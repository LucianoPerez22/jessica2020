<?php

namespace App\Security;

use App\Entity\User as AppUser;
use DateTime;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(RequestStack $requestStack, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isEnabled()){
            throw new CustomUserMessageAuthenticationException($this->translator->trans('flash.common.error_user_enabled'));
        }

        if ($user->isSuperAdmin()){
            return;
        }
        
        if ($this->entityManager->getRepository(AppUser::class)->esConsumidor($user->getId())) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('flash.common.error_unauthorized'));
        }
        
        if ($this->entityManager->getRepository(AppUser::class)->esRepartidor($user->getId())) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('flash.common.error_unauthorized'));
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        $user->setLastLogin(new DateTime('now'));
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }
}
