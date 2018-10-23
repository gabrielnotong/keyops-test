<?php
namespace App\Controller;

use App\Entity\Company;
use App\Exception\ResourceValidationException;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @Rest\Route("api")
 * Class CompanyController
 * @package App\Controller
 */
class CompanyController extends BaseController
{
    /**
     * @Rest\Get("/companies", name="company_list")
     * @SWG\Get(
     *     summary="Get companies info"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if companies list is ok",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Company::class, groups={"create"}))
     *     )
     * )
     * @SWG\Tag(name="companies")
     * @param CompanyRepository $repository
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return View
     */
    public function list(Request $request, CompanyRepository $repository, PaginatorInterface $paginator): View
    {
        $pager = $repository->search(
            $paginator,
            $request
        );

        return View::create($pager, Response::HTTP_OK , []);
    }


    /**
     * @Rest\Post(path="/companies", name="company_add")
     * @SWG\Post(
     *     summary="Creates a company"
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Returned if the company has been successfully created",
     *     @SWG\Schema(
     *         ref=@Model(type=Company::class),
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Company::class, groups={"create"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="Body",
     *     in="body",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="name", type="string"),
     *         @SWG\Property(property="address", type="string"),
     *         @SWG\Property(property="phone", type="string"),
     *         @SWG\Property(property="email", type="string"),
     *         @SWG\Property(property="description", type="string")
     *      )
     * )
     * @SWG\Tag(name="companies")
     * @ParamConverter(
     *     "company",
     *     converter="fos_rest.request_body",
     *     options={
     *          "validator"={"groups"="create"}
     *     }
     * )
     * @param Company $company
     * @param EntityManagerInterface $manager
     * @param ConstraintViolationList $violations
     * @return View
     * @throws ResourceValidationException
     */
    public function create(Company $company, EntityManagerInterface $manager, ConstraintViolationList $violations): View
    {
        parent::validateEntity($violations);

        $manager->persist($company);
        $manager->flush();

        return View::create($company, Response::HTTP_CREATED , []);
    }

    /**
     * @Rest\Get(path="/companies/{id}", name="company_show", requirements={"id"="\d+"})
     * @SWG\Get(
     *     summary="Show a company"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if company is found",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Company::class, groups={"create"}))
     *     )
     * )
     * @SWG\Tag(name="companies")
     * @param Request $request
     * @param CompanyRepository $repository
     * @return View
     */
    public function show(Request $request, CompanyRepository $repository): View
    {
        $company = $repository->findOneBy(['id' => $request->get('id')]);
        $this->notFound($company, 'Company not found');
        return View::create($company, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Put(path="/companies/{id}", name="company_update", requirements={"id"="\d+"})
     * @SWG\Put(
     *     summary="Updates Company informations"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if Company informations has been success fully updated",
     *     @SWG\Schema(
     *         ref=@Model(type=Company::class),
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Company::class, groups={"create"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="Body",
     *     in="body",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="name", type="string"),
     *         @SWG\Property(property="address", type="string"),
     *         @SWG\Property(property="phone", type="string"),
     *         @SWG\Property(property="email", type="string"),
     *         @SWG\Property(property="description", type="string")
     *      )
     * )
     * @SWG\Tag(name="companies")
     * @param CompanyRepository $repository
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return View
     */
    public function update(EntityManagerInterface $manager, CompanyRepository $repository, Request $request): View
    {
        $company = $repository->findOneBy(['id' => $request->get('id')]);

        $this->notFound($company, 'Company not found');

        $form = $this->createForm(CompanyType::class, $company);
        $form->submit($request->request->all());

        $manager->persist($company);
        $manager->flush();

        return View::create($company, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Delete(path="/companies/{id}", name="company_delete", requirements={"id"="\d+"})
     * @SWG\Delete(
     *     summary="Deletes a company"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if company deleted",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Company::class, groups={"create"}))
     *     )
     * )
     * @SWG\Tag(name="companies")
     * @param int $id
     * @param CompanyRepository $repository
     * @param EntityManagerInterface $manager
     * @return View
     */
    public function delete(int $id, EntityManagerInterface $manager, CompanyRepository $repository): View
    {
        $this->deleteEntity($id, 'Company not found', $repository, $manager);
        return View::create(['success' => 'The company has been deleted'], Response::HTTP_OK , []);
    }
}
