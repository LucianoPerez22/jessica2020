<?php

namespace App\Repository;

use App\Entity\VentasRecurrentes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fields|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fields|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fields[]    findAll()
 * @method Fields[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VentasRecurrentesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VentasRecurrentes::class);
    }


    public function findLastNumber()
    {
        $q = $this->createQueryBuilder('v')
            ->select('Max(v.numero)');

        return $q->getQuery();
    }

    public function findByDate($desde, $hasta)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.fecha BETWEEN :desde AND :hasta')
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta);
        return $qb->getQuery()->getResult();
    }
}