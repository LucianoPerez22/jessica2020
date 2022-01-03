<?php

namespace App\Repository;

use App\Entity\Articulos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fields|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fields|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fields[]    findAll()
 * @method Fields[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticulosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Articulos::class);
    }
                    
    public function findAllWithFilterAndOrderQuery(array $filters, array $order=null)
    {       
        $q = $this->createQueryBuilder('a')
            ->select('a');          
            
            if (!empty($filters['name'])) {
                $name = '%' . $filters['name'] . '%';
                $q->andWhere('a.descripcion LIKE :name')
                ->setParameter('name', $name);
            }
           
        return $q->getQuery();                                            
    }    
}