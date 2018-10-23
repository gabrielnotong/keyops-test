<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
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
     * @SWG\Get(
     *     summary="Get users info"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if users list is ok",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"create"}))
     *     )
     * )
     * @SWG\Tag(name="users")
     * @param UserRepository $repository
     * @return View
     */
    public function list(UserRepository $repository): View
    {
        return View::create($repository->findAll(), Response::HTTP_OK , []);
    }

    /**
     * @Rest\Post(path="/users", name="user_add")
     *  @SWG\Post(
     *     summary="Create new user"
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Returned if the user has been successfully created",
     *     @SWG\Schema(
     *         ref=@Model(type=User::class),
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"create"}))
     *     )
     * )
     * @SWG\Tag(name="users")
     * @SWG\Parameter(
     *     name="Body",
     *     in="body",
     *     required=true,
     *     description="All fields are mandatory. Please enter existing comapany name",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="firstname", type="string"),
     *         @SWG\Property(property="lastname", type="string"),
     *         @SWG\Property(property="email", type="string"),
     *         @SWG\Property(
     *            property="company",
     *            type="object",
     *            @SWG\Property(property="name", type="string")
     *         )
     *      )
     * )
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
        $this->validateEntity($violations);

        $company = $repository->findOneBy(['name' => $user->getCompany()->getName()]);

        $this->notFound($company, 'The user company does not exist');

        $user->setCompany($company);

        $manager->persist($user);
        $manager->flush();

        return View::create($user, Response::HTTP_CREATED , []);
    }

    /**
     * @Rest\Get(path="/users/{id}", name="user_show", requirements={"id"="\d+"})
     *  @SWG\Get(
     *     summary="Show a user"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if user is found",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"create"}))
     *     )
     * )
     * @SWG\Tag(name="users")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return View
     */
    public function show(Request $request, UserRepository $userRepository): View
    {
        $user = $userRepository->findOneBy(['id' => $request->get('id')]);
        $this->notFound($user, 'User not found');

        return View::create($user, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Put(path="/users/{id}", name="user_update", requirements={"id"="\d+"})
     *  @SWG\Put(
     *     summary="Updates user informations"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if user informations has been success fully updated",
     *     @SWG\Schema(
     *         ref=@Model(type=User::class),
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"create"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="Body",
     *     in="body",
     *     required=true,
     *     description="All user fields are mandatory. If you want to change user comapny, please enter an existing comapny name",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="firstname", type="string"),
     *         @SWG\Property(property="lastname", type="string"),
     *         @SWG\Property(property="email", type="string"),
     *         @SWG\Property(
     *            property="company",
     *            type="object",
     *            @SWG\Property(property="name", type="string")
     *         )
     *      )
     * )
     * @SWG\Tag(name="users")
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

        $this->hydrateUser($user, $request->request->all());

        $manager->persist($user);
        $manager->flush();

        return View::create($user, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Delete(path="/users/{id}", name="user_delete", requirements={"id"="\d+"})
     *  @SWG\Delete(
     *     summary="Deletes a user"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned if user deleted",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"create"}))
     *     )
     * )
     * @SWG\Tag(name="users")
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
