<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Rarity;
use App\Struct\Order;
use App\Struct\PaginationInfo;
use App\Struct\ProbabilityEntryCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

        return new Paginator($qb, fetchJoinCollection: true);
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
            ->select('count(i.id)');

        $qb = $this->handleSearchTerm($qb, $paginationInfo);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllItemIndividualRarities(UserInterface $owner, EntityManagerInterface $em, array $rarities): ProbabilityEntryCollection
    {
        $conn = $em->getConnection();
        $sql = "select 
                    i.id, 
                    i.parent_id, 
                    i.name,
                    r.value as rarity_value,
                    CONVERT(r.value / (select sum(r2.value) as rarity_sum 
                               from item i2 
                                   left join rarity r2 
                                       on i2.rarity_id = r2.id 
                               where (i.parent_id = i2.parent_id) 
                                 and i2.owner_id = " . $owner->getId() . " 
                               group by i.parent_id ), DOUBLE) as individual_probability 
                    from item i 
                        left join rarity r
                            on i.rarity_id = r.id
                    where i.owner_id = " . $owner->getId() . $this->getRarities($rarities) ."
                    
                    order by i.parent_id";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        $collection = new ProbabilityEntryCollection();

        return $collection->buildCollectionFromSQLResult($result->fetchAllAssociative());
    }

    public function getItemsById(UserInterface $owner, array $ids)
    {
        $qb = $this->getDefaultQueryBuilder($owner)
            ->addSelect('r')
            ->leftJoin('i.rarity', 'r')
            ->andWhere('i.id in (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()
            ->getResult();
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

    /**
     * @param Rarity[] $rarities
     * @return string
     */
    private function getRarities(array $rarities)
    {
        if (count($rarities) <= 0) {
            return "";
        }

        $output = "";
        foreach ($rarities as $rarity){
            $output .= $rarity->getId() . ",";
        }

        return " AND r.id IN (" . trim($output, ",") . ")";
    }
}
