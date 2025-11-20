<?php

namespace App\Repository;

use App\Entity\Figure;
use App\Entity\Member;
use App\Entity\Arena;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Figure>
 */
class FigureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Figure::class);
    }
    
    /**
     * Ajoute une figure (méthode standard du maker)
     */
    public function add(Figure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Suppression propre d'une figure :
     * - enlève la Figure de toutes les Arenas associées (ManyToMany)
     * - puis supprime la Figure elle-même
     */
    public function remove(Figure $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        
        // 1) Nettoyer la relation ManyToMany Figure <-> Arena
        foreach ($entity->getArenas() as $arena) {
            /** @var Arena $arena */
            $arena->removeFigure($entity);
            $em->persist($arena);
        }
        
        if ($flush) {
            $em->flush();
        }
        
        // 2) Supprimer la figure elle-même
        $em->remove($entity);
        
        if ($flush) {
            $em->flush();
        }
    }
    
    /**
     * @return Figure[] Returns an array of Figure objects for a member
     */
    public function findMemberFigures(Member $member): array
    {
        return $this->createQueryBuilder('f')
        ->leftJoin('f.vitrine', 'v')
        ->andWhere('v.owner = :member')
        ->setParameter('member', $member)
        ->getQuery()
        ->getResult()
        ;
    }
    
    //    /**
    //     * @return Figure[] Returns an array of Figure objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    
    //    public function findOneBySomeField($value): ?Figure
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
