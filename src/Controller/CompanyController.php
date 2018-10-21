<?php
namespace App\Controller;

use App\Entity\Company;
use App\Exception\ResourceValidationException;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @param CompanyRepository $repository
     * @return View
     */
    public function list(CompanyRepository $repository): View
    {
        return View::create($repository->findAll(), Response::HTTP_OK , []);
    }

    /**
     * @Rest\Post(path="/companies", name="company_add")
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
     * @Rest\Get(path="/companies/{id}", name="company_show")
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
     * @Rest\Put(path="/companies/{id}", name="company_update")
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

        if ($form->isValid()) {
            $manager->persist($company);
            $manager->flush();
        }

        return View::create($company, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Delete(path="/companies/{id}", name="company_delete")
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
