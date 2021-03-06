<?php

namespace App\Repository;

use App\Entity\Marcas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fields|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fields|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fields[]    findAll()
 * @method Fields[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarcasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Marcas::class);
    }
                    
    public function findAllWithFilterAndOrderQuery(array $filters, array $order=null)
    {       
        $q = $this->createQueryBuilder('m')
            ->select('m');          
            
            if (!empty($filters['name'])) {
                $name = '%' . $filters['name'] . '%';
                $q->andWhere('m.descripcion LIKE :name')
                ->setParameter('name', $name);
            }
           
        return $q->getQuery();                                            
    }    
}
