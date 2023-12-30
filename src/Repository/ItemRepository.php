<?php

namespace App\Repository;

use App\Entity\Item;
use App\Struct\Order;
use App\Struct\PaginationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    public function getItemsByOwner(UserInterface $owner, PaginationInfo $paginationInfo, ?Order $order = Order::NAME_ASC)
    {
        $order = $order ?? Order::NAME_ASC;

        $qb = $this->getDefaultQueryBuilder($owner)
            ->setMaxResults($paginationInfo->getPageSize())
            ->setFirstResult(($paginationInfo->getPage() - 1) * $paginationInfo->getPageSize())
            ->leftJoin('i.rarity', 'r')
            ->leftJoin('i.parent', 'p')
            ->addSelect('r')
            ->addSelect('p');

        $qb = $this->handleOrder($qb, $order);
        $qb = $this->handleSearchTerm($qb, $paginationInfo);

        return $qb->getQuery()
            ->execute();
    }

    /**
     * @param UserInterface $owner
     * @param PaginationInfo $paginationInfo
     * @return float|bool|int|string|null
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getItemsCountByOwner(UserInterface $owner, PaginationInfo $paginationInfo): float|bool|int|string|null
    {
        $qb = $this->getDefaultQueryBuilder($owner)
            ->setMaxResults($paginationInfo->getPageSize())
            ->setFirstResult(($paginationInfo->getPage() - 1) * $paginationInfo->getPageSize())
            ->select('count(i.id)');

        $qb = $this->handleSearchTerm($qb, $paginationInfo);

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function getDefaultQueryBuilder(UserInterface $owner): QueryBuilder
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner = :owner')
            ->setParameter('owner', $owner);
    }

    protected function handleSearchTerm(QueryBuilder $qb, PaginationInfo $paginationInfo): QueryBuilder
    {
        if (!empty($paginationInfo->getSearchTerm())) {
            $qb->andWhere('i.name like :term OR i.description like :term')
                ->setParameter('term', '%' . $paginationInfo->getSearchTerm() . '%');
        }

        return $qb;
    }

    protected function handleOrder(QueryBuilder $qb, Order $order): QueryBuilder
    {
        switch ($order) {
            case Order::NAME_DESC:
                $qb->orderBy('i.name', 'DESC');
                break;
            case Order::RARITY_ASC:
                $qb->orderBy('r.value', 'DESC'); // lowest value is actually the highest rarity
                break;
            case Order::RARITY_DESC:
                $qb->orderBy('r.value', 'ASC'); // lowest value is actually the highest rarity
                break;
            case Order::NAME_ASC:
            default:
                $qb->orderBy('i.name', 'ASC');
                break;
        }

        return $qb;
    }

}
