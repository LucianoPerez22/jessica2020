<?php

namespace App\Repository;

use App\Entity\Presupuestos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Presupuesto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Presupuesto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Presupuesto[]    findAll()
 * @method Presupuesto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PresupuestoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presupuestos::class);
    }

    public function findAllWithFilterAndOrderQuery(array $filters, array $order=null)
    {       
        $q = $this->createQueryBuilder('p')
            ->select('p')
            ->addOrderBy('p.id', 'desc');          
            
            if (!empty($filters['name'])) {
                $name = '%' . $filters['name'] . '%';
                $q->andWhere('p.cliente LIKE :name')                   
                ->setParameter('name', $name);
            }
           
        return $q->getQuery();                                            
    }    

    public function findLastNumber()
    {              
        $q = $this->createQueryBuilder('p')
            ->select('Max(p.id)');                                                                  
           
        return $q->getQuery();                                            
    }    
}
