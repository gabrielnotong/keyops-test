<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @Rest\Route("api")
 * Class UserController
 * @package App\Controller
 */
class UserController extends BaseController
{
    /**
     * @Rest\Get("/users", name="user_list")
     * @param UserRepository $repository
     * @return View
     */
    public function list(UserRepository $repository): View
    {
        return View::create($repository->findAll(), Response::HTTP_OK , []);
    }

    /**
     * @Rest\Post(path="/users", name="user_add")
     * @ParamConverter(
     *     "user",
     *     converter="fos_rest.request_body",
     *     options={
     *          "validator"={"groups"="create"}
     *     }
     * )
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param CompanyRepository $repository
     * @param ConstraintViolationList $violations
     * @return View
     * @throws \App\Exception\ResourceValidationException
     */
    public function create(User $user, EntityManagerInterface $manager, CompanyRepository $repository, ConstraintViolationList $violations): View
    {
        parent::validateEntity($violations);

        $company = $repository->findOneBy(['name' => $user->getCompany()->getName()]);

        $this->notFound($user, 'The user company does not exist');

        $user->setCompany($company);

        $manager->persist($user);
        $manager->flush();

        return View::create($user, Response::HTTP_CREATED , []);
    }

    /**
     * @Rest\Get(path="/users/{id}", name="user_show")
     * @param User $user
     * @return View
     */
    public function show(User $user): View
    {
        return View::create($user, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Put(path="/users/{id}", name="user_update")
     * @param UserRepository $userRepository
     * @param CompanyRepository $companyRepository
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return View
     */
    public function update(EntityManagerInterface $manager, UserRepository $userRepository, CompanyRepository $companyRepository, Request $request): View
    {
        $user = $userRepository->findOneBy(['id' => $request->get('id')]);

        if (isset($request->get('company')['name'])) {
            $company = $companyRepository->findOneBy(['name' => $request->get('company')['name']]);

            $this->notFound($company, 'The user company does not exist');

            $user->setCompany($company);
        }

        $this->notFound($user, 'User not found');

        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all());
        $manager->persist($user);
        $manager->flush();

        return View::create($user, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Delete(path="/users/{id}", name="user_delete")
     * @param int $id
     * @param UserRepository $repository
     * @param EntityManagerInterface $manager
     * @return View
     */
    public function delete(int $id, EntityManagerInterface $manager, UserRepository $repository): View
    {
        $this->deleteEntity($id, 'User not found', $repository, $manager);
        return View::create(['success' => 'The User has been deleted'], Response::HTTP_OK , []);
    }
}
