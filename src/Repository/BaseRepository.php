<?php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;


abstract class BaseRepository extends ServiceEntityRepository
{
    /**
     * Defines the number of company objects per page
     * @param PaginatorInterface $paginator
     * @param QueryBuilder $qb
     * @param string $limit = number of elements per page
     * @param string $offset = page number
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    protected function paginate(PaginatorInterface $paginator, QueryBuilder $qb, string $limit, string $offset)
    {
        return $paginator->paginate($qb, $offset, $limit);
    }
}
