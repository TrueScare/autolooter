<?php

namespace App\Repository;

use App\Entity\User;
use App\Struct\Order;
use App\Struct\PaginationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getUsers(PaginationInfo $paginationInfo, $order = Order::NAME_ASC)
    {
        $order = $order ?? Order::NAME_ASC;

        $qb = $this->createQueryBuilder('u')
            ->setMaxResults($paginationInfo->getPageSize())
            ->setFirstResult(($paginationInfo->getPage() - 1) * $paginationInfo->getPageSize())
           ;

        $qb = $this->handleSearchTerm($qb, $paginationInfo);
        $qb = $this->handleOrder($qb, $order);

        return new Paginator($qb, fetchJoinCollection: true);
    }

    public function getUserCount(PaginationInfo $paginationInfo): float|bool|int|string|null
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)');

        $qb = $this->handleSearchTerm($qb,$paginationInfo);

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    private function handleSearchTerm(QueryBuilder $qb, PaginationInfo $paginationInfo): QueryBuilder
    {
        if (!empty($paginationInfo->getSearchTerm())) {
            $qb->orWhere('u.username like :term')
                ->setParameter('term', '%' . $paginationInfo->getSearchTerm() . '%');
        }

        return $qb;
    }

    private function handleOrder(QueryBuilder $qb, Order $order): QueryBuilder
    {
        switch ($order) {
            case Order::NAME_DESC:
                $qb->orderBy('u.username', 'DESC');
                break;
            case Order::LOGIN_ASC:
                $qb->orderBy('u.last_login', 'ASC');
                break;
            case Order::LOGIN_DESC:
                $qb->orderBy('u.last_login', 'DESC');
                break;
            case Order::NAME_ASC:
            default:
                $qb->orderBy('u.username', 'ASC');
                break;
        }

        return $qb;
    }
}
