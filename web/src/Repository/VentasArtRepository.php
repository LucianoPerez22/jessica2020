<?php

namespace App\Repository;

use App\Entity\VentasArt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fields|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fields|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fields[]    findAll()
 * @method Fields[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VentasArtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VentasArt::class);
    }            

    public function findByDate($desde, $hasta){
        $qb = $this->createQueryBuilder('va')
                        ->select('va', 'v', 'SUM(va.cant) as total')                        
                        ->innerJoin('va.idVentas', 'v' )
                        ->where('v.fecha BETWEEN :desde AND :hasta')                        
                        ->setParameter('desde', $desde)
                        ->setParameter('hasta', $hasta)
                        ->groupBy("va.idArt")
                        ->orderBy('total', 'DESC')
                        ;
        return $qb->getQuery()->getResult();                
    }

    public function findByDateAndName($desde, $hasta){
        $qb = $this->createQueryBuilder('va')
                        ->select('va', 'v', 'SUM(va.cant) as total')                        
                        ->innerJoin('va.idVentas', 'v' )
                        ->where('v.fecha BETWEEN :desde AND :hasta AND va.description')                        
                        ->setParameter('desde', $desde)
                        ->setParameter('hasta', $hasta)
                        ->groupBy("va.idArt")
                        ->orderBy('total', 'DESC')
                        ;
        return $qb->getQuery()->getResult();   
        
        /* QUERY RESUELTA PARA LA FUNCION
            SELECT jessica.ventas.*
            FROM jessica.ventas
            INNER JOIN jessica.ventas_art ON jessica.ventas.id = jessica.ventas_art.id_ventas 
            WHERE ventas.fecha BETWEEN '2021-12-01' AND '2021-12-15' AND jessica.ventas_art.id_art = 34
            GROUP BY jessica.ventas_art.id_art;
        */
    }
}