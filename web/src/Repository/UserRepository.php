<?php

namespace App\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface, UserProviderInterface
{
    /**
     * @param $username
     * @return mixed|null|\Symfony\Component\Security\Core\User\UserInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllExceptThis($id){

        $q = $this->createQueryBuilder('u')
            ->select('u,g,a')
            ->leftJoin('u.groups', 'g')
            ->orderBy('u.name', 'ASC');

        $q->andWhere('u.id != :id')
            ->setParameter('id', $id);

        return $q;
    }

    public function findAllWithFilterAndOrderQuery($filters, $order)
    {
        $q = $this->createQueryBuilder('u')
            ->select('u,g')
            ->leftJoin('u.groups', 'g')
            ->orderBy('u.name', 'ASC');

        if (!empty($filters['name'])) {
            $name = '%' . $filters['name'] . '%';
            $q->andWhere('(u.name LIKE :name OR u.lastName LIKE :name)')
                ->setParameter('name', $name);
        }

        if (!empty($filters['username'])) {
            $username = '%' . $filters['username'] . '%';
            $q->andWhere('u.username LIKE :username')
                ->setParameter('username', $username);
        }

        if (!empty($filters['email'])) {
            $email = '%' . $filters['email'] . '%';
            $q->andWhere('u.email LIKE :email')
                ->setParameter('email', $email);
        }
        if (isset($filters['enabled']) && $filters['enabled'] !== "") {
            $q->andWhere('u.enabled = :enabled')
                ->setParameter('enabled', $filters['enabled']);
        }

        return $q->getQuery();
    }

    public function findAllQuery($order = [])
    {
        return $this->findAllWithFilterAndOrderQuery([], $order);
    }

    /**
     * @param UserInterface $user
     * @return mixed|null|UserInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Surgeworks\CoreBundle\Entity\User';
    }
}
