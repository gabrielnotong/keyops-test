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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\Route("api")
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
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
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param CompanyRepository $repository
     * @return View
     */
    public function create(User $user, EntityManagerInterface $manager, CompanyRepository $repository): View
    {
        $company = $repository->findOneBy(['name' => $user->getCompany()->getName()]);

        if (empty($company)) {
            return View::create(['error' => 'The user company does not exist'], Response::HTTP_NOT_FOUND);
        }

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

            if (empty($company)) {
                return View::create(['error' => 'The user company does not exist'], Response::HTTP_NOT_FOUND);
            }
            $user->setCompany($company);
        }

        if (empty($user)) {
            return View::create(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

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
        $user = $repository->findOneBy(['id' => $id]);

        if (empty($user)) {
            return View::create(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $manager->remove($user);
        $manager->flush();

        return View::create(['success' => 'The User has been deleted'], Response::HTTP_OK , []);
    }
}
