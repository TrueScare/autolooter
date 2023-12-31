<?php

namespace App\Repository;

use App\Entity\Rarity;
use App\Struct\Order;
use App\Struct\PaginationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * @param UserInterface $owner
     * @param PaginationInfo $paginationInfo
     * @param Order|null $order
     * @return mixed
     */
    public function getRaritiesByOwner(UserInterface $owner, PaginationInfo $paginationInfo, ?Order $order = Order::NAME_ASC): mixed
    {
        $order = $order ?? Order::NAME_ASC;

        $qb = $this->getDefaultQueryBuilder($owner)
            ->leftJoin('r.items', 'i')
            ->leftJoin('r.tables', 't')
            ->addSelect('t')
            ->addSelect('i')
            ->setMaxResults($paginationInfo->getPageSize())
            ->setFirstResult(($paginationInfo->getPage() - 1) * $paginationInfo->getPageSize());

        $qb = $this->handleSearchTerm($qb, $paginationInfo);
        $qb = $this->handleOrder($qb, $order);

        return new Paginator($qb, fetchJoinCollection: true);
    }

    /**
     * @param UserInterface $owner
     * @param PaginationInfo $paginationInfo
     * @return float|bool|int|string|null
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getRarityCountByOwner(UserInterface $owner, PaginationInfo $paginationInfo): float|bool|int|string|null
    {
        $qb = $this->getDefaultQueryBuilder($owner)
            ->select('count(r.id)')
            ->setMaxResults($paginationInfo->getPageSize())
            ->setFirstResult(($paginationInfo->getPage() - 1) * $paginationInfo->getPageSize());

        $qb = $this->handleSearchTerm($qb, $paginationInfo);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param UserInterface $owner
     * @return QueryBuilder
     */
    protected function getDefaultQueryBuilder(UserInterface $owner): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.owner = :owner')
            ->setParameter('owner', $owner);
    }

    /**
     * @param QueryBuilder $qb
     * @param Order $order
     * @return QueryBuilder
     */
    protected function handleOrder(QueryBuilder $qb, Order $order): QueryBuilder
    {
        switch ($order) {
            case Order::NAME_ASC:
                $qb->orderBy('r.name', 'ASC');
                break;
            case Order::NAME_DESC:
                $qb->orderBy('r.name', 'DESC');
                break;
            case Order::RARITY_ASC:
                $qb->orderBy('r.value', 'DESC'); // lowest value is actually the highest rarity
                break;
            case Order::RARITY_DESC:
                $qb->orderBy('r.value', 'ASC'); // lowest value is actually the highest rarity
                break;
        }

        return $qb;
    }

    protected function handleSearchTerm(QueryBuilder $qb, PaginationInfo $paginationInfo): QueryBuilder
    {
        if (!empty($paginationInfo->getSearchTerm())) {
            $qb->andWhere('r.name like :term OR r.description like :term')
                ->setParameter('term', '%' . $paginationInfo->getSearchTerm() . '%');
        }

        return $qb;
    }

}
