<?php
namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Rest\Route("api")
 * Class CompanyController
 * @package App\Controller
 */
class CompanyController extends FOSRestController
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
     * @ParamConverter("company", converter="fos_rest.request_body")
     * @param Company $company
     * @param EntityManagerInterface $manager
     * @return View
     */
    public function create(Company $company, EntityManagerInterface $manager): View
    {
        $manager->persist($company);
        $manager->flush();

        return View::create($company, Response::HTTP_CREATED , []);
    }

    /**
     * @Rest\Get(path="/companies/{id}", name="company_show")
     * @param Company $company
     * @return View
     */
    public function show(Company $company): View
    {
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

        if (empty($company)) {
            return View::create(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
        }

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
        $company = $repository->findOneBy(['id' => $id]);

        if (empty($company)) {
            return View::create(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
        }

        $manager->remove($company);
        $manager->flush();

        return View::create(['success' => 'The company has been deleted'], Response::HTTP_OK , []);
    }
}
