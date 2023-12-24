<?php

namespace App\Repository;

use App\Entity\Rarity;
use App\Service\OrderService;
use App\Struct\PaginationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Rarity>
 *
 * @method Rarity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rarity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rarity[]    findAll()
 * @method Rarity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RarityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rarity::class);
    }

    public function getRaritiesByOwner(UserInterface $owner, PaginationInfo $paginationInfo, $order = OrderService::NAME_ASC)
    {
        $qb = $this->getDefaultQueryBuilder($owner)
            ->setMaxResults($paginationInfo->getPageSize())
            ->setFirstResult(($paginationInfo->getPage() - 1) * $paginationInfo->getPageSize());

        switch ($order) {
            case OrderService::NAME_ASC:
                $qb->orderBy('r.name', 'ASC');
                break;
            case OrderService::NAME_DESC:
                $qb->orderBy('r.name', 'DESC');
                break;
            case OrderService::RARITY_ASC:
                $qb->orderBy('r.value', 'DESC'); // lowest value is actually the highest rarity
                break;
            case OrderService::RARITY_DESC:
                $qb->orderBy('r.value', 'ASC'); // lowest value is actually the highest rarity
                break;
        }

        if ($paginationInfo->getSearchTerm() !== null)   {
            $qb->andWhere('r.name like :term')
                ->setParameter('term', '%' . $paginationInfo->getSearchTerm() . '%');
        }

        return $qb->getQuery()
            ->execute();
    }

    public function getRarityCountByOwner(UserInterface $owner)
    {
        return $this->getDefaultQueryBuilder($owner)
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getDefaultQueryBuilder(UserInterface $owner): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.owner = :owner')
            ->setParameter('owner', $owner);
    }
//    /**
//     * @return Rarity[] Returns an array of Rarity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Rarity
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
