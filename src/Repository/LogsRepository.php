<?php

namespace App\Repository;

use App\Entity\Logs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Logs>
 *
 * @method Logs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logs[]    findAll()
 * @method Logs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logs::class);
    }
    public function findNowszeNiżIOStatusieError(): array
    {
        $qb = $this->createQueryBuilder('l')
            ->andWhere('l.status = :status')
            ->setParameter('status', '1')
            ;
        $query = $qb->getQuery();
        return $query->execute();
    }
    public function setStatusWysłano($id): void
    {
        $qb = $this->createQueryBuilder('l')
            ->update()
            ->set('l.status', '2')
            ->andWhere('l.id = :id')
            ->setParameter('id', $id)
            ;
        
        $query = $qb->getQuery();
        $query->execute();
    }
    public function findCzyLogWystepujeDzis($today, $message, $id): bool
    {
        $qb = $this->createQueryBuilder('l')
            ->andWhere('l.time_stamp > :today')
            ->setParameter('today', $today)
            ->andWhere('l.message = :message')
            ->setParameter('message', $message)
            ->andWhere('l.id != :id')
            ->setParameter('id', $id)
            ;
        $query = $qb->getQuery();
        $isThere = $query->execute();
        return ($isThere) ? true : false;
    }

    public function findWiększeNiżIdIStatusieError($id): array
    {
        $qb = $this->createQueryBuilder('l')
            ->andWhere('l.status = :status')
            ->setParameter('status', '1')
            ->andWhere('l.id > :id')
            ->setParameter('id', $id)
            ;
        $query = $qb->getQuery();
        return $query->execute();
    }

//    /**
//     * @return Logs[] Returns an array of Logs objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Logs
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
