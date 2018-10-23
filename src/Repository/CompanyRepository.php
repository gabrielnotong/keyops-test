<?php
namespace App\Repository;

use App\Entity\Company;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function createFindAllQuery()
    {
        return $this->createQueryBuilder('company');
    }

    /**
     * Searches companies by applying filters
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Knp\Component\Pager\Pagination\PaginationInterface*
     */
    public function search(PaginatorInterface $paginator, Request $request)
    {
        // Filter on company name
        $term = empty($request->query->getAlnum('filter')) ? null : $request->query->getAlnum('filter');
        // order the result. default is 'asc'
        $order =  empty($request->query->getAlnum('order')) ? 'asc' : $request->query->getAlnum('order');
        // number of elements per page
        $limit = empty($request->query->getAlnum('limit')) ? 3 : $request->query->getAlnum('limit');
        // page number
        $offset = empty($request->query->getAlnum('offset')) ? 1 : $request->query->getAlnum('offset');

        $qb = $this
            ->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.name', $order)
        ;

        if ($term) {
            $qb
                ->where('c.name LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
            ;
        }

        return $this->paginate($paginator, $qb, $limit, $offset);
    }
}
