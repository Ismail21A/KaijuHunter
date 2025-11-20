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
        
        foreach ($entity->getArenas() as $arena) {
            /** @var Arena $arena */
            $arena->removeFigure($entity);
            $em->persist($arena);
        }
        
        if ($flush) {
            $em->flush();
        }
        
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
    
    
}
