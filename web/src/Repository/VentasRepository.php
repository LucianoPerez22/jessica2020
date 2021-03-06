<?php

namespace App\Repository;

use App\Entity\Ventas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fields|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fields|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fields[]    findAll()
 * @method Fields[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VentasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ventas::class);
    }
        
    public function findAllWithFilterAndOrderQuery(array $filters, array $order=null)
    {    
        $q = $this->createQueryBuilder('v')
            ->select('v', 'c')  
            ->innerJoin('v.idCliente', 'c' )        
            ->addOrderBy("v.id", "desc");

            if (!empty($filters['name'])) {
                $name = '%' . $filters['name'] . '%';
                $q->andWhere('c.nombre LIKE :name')
                ->setParameter('name', $name);               
            }
        
        return $q->getQuery();                                            
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