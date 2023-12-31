<?php

namespace App\Repository;

use App\Entity\Table;
use App\Struct\Order;
use App\Struct\PaginationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Table>
 *
 * @method Table|null find($id, $lockMode = null, $lockVersion = null)
 * @method Table|null findOneBy(array $criteria, array $orderBy = null)
 * @method Table[]    findAll()
 * @method Table[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Table::class);
    }

    public function getAllTablesByOwner(UserInterface $owner)
    {
        return $this->getDefaultQueryBuilder($owner)
            ->leftJoin('t.rarity', 'r')
            ->leftJoin('t.parent', 'p')
            ->addSelect('r')
            ->addSelect('p')
            ->getQuery()
            ->execute();
    }

    public function getTablesByOwner(UserInterface $owner, PaginationInfo $paginationInfo, $order = Order::NAME_ASC)
    {
        $order = $order ?? Order::NAME_ASC;

        $qb = $this->getDefaultQueryBuilder($owner)
            ->setMaxResults($paginationInfo->getPageSize())
            ->setFirstResult(($paginationInfo->getPage() - 1) * $paginationInfo->getPageSize())
            ->leftJoin('t.rarity', 'r')
            ->leftJoin('t.parent', 'p')
            ->leftJoin('t.items', 'i')
            ->leftJoin('t.tables', '_t')
            ->addSelect('r')
            ->addSelect('p')
            ->addSelect('i')
            ->addSelect('_t');

        $qb = $this->handleSearchTerm($qb, $paginationInfo);
        $qb = $this->handleOrder($qb, $order);

        return new Paginator($qb, fetchJoinCollection: true);
    }

    public function getTableCountByOwner(UserInterface $owner, PaginationInfo $paginationInfo)
    {
        $qb = $this->getDefaultQueryBuilder($owner)
            ->select('count(t.id)');

        $qb = $this->handleSearchTerm($qb, $paginationInfo);

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    protected function getDefaultQueryBuilder(UserInterface $owner): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner);
    }

    protected function handleOrder(QueryBuilder $qb, Order $order): QueryBuilder
    {
        switch ($order) {
            case Order::NAME_DESC:
                $qb->orderBy('t.name', 'DESC');
                break;
            case Order::RARITY_ASC:
                $qb->orderBy('t.value', 'DESC'); // lowest value is actually the highest rarity
                break;
            case Order::RARITY_DESC:
                $qb->orderBy('t.value', 'ASC'); // lowest value is actually the highest rarity
                break;
            case Order::NAME_ASC:
            default:
                $qb->orderBy('t.name', 'ASC');
                break;
        }

        return $qb;
    }

    protected function handleSearchTerm(QueryBuilder $qb, PaginationInfo $paginationInfo): QueryBuilder
    {
        if (!empty($paginationInfo->getSearchTerm())) {
            $qb->andWhere('t.name like :term OR t.description like :term')
                ->setParameter('term', '%' . $paginationInfo->getSearchTerm() . '%');
        }

        return $qb;
    }


}
