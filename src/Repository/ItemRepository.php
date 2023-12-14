<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\User;
use App\Service\OrderService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Item>
 *
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function getItemsByOwner(UserInterface $owner, $page = 1, $pageSize = 25, $order = OrderService::NAME_ASC)
    {
        $qb = $this->getDefaultQueryBuilder($owner)
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize)
            ->leftJoin('i.rarity', 'r')
            ->addSelect('r');

        switch ($order) {
            case OrderService::NAME_ASC:
                $qb->orderBy('i.name', 'ASC');
                break;
            case OrderService::NAME_DESC:
                $qb->orderBy('i.name', 'DESC');
                break;
            case OrderService::RARITY_ASC:
                $qb->orderBy('r.value', 'DESC'); // lowest value is actually the highest rarity
                break;
            case OrderService::RARITY_DESC:
                $qb->orderBy('r.value', 'ASC'); // lowest value is actually the highest rarity
                break;
        }

        return $qb->getQuery()
            ->execute();
    }

    public function getItemsCountByOwner(UserInterface $owner)
    {
        return $this->getDefaultQueryBuilder($owner)
            ->select('count(i.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getDefaultQueryBuilder(UserInterface $owner): QueryBuilder
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner = :owner')
            ->setParameter('owner', $owner);
    }

//    /**
//     * @return Item[] Returns an array of Item objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Item
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
