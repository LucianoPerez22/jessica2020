<?php

namespace App\Repository;

use App\Entity\ListaDeUsuarios;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fields|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fields|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fields[]    findAll()
 * @method Fields[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListaDeUsuarios::class);
    }
                    
    public function findAllWithFilterAndOrderQuery(array $filters, array $order=null)
    {       
        $q = $this->createQueryBuilder('c')
            ->select('c');          
            
            if (!empty($filters['name'])) {
                $name = '%' . $filters['name'] . '%';
                $q->andWhere('c.nombre LIKE :name')
                ->setParameter('name', $name);
            }
           
        return $q->getQuery();                                            
    }    

}