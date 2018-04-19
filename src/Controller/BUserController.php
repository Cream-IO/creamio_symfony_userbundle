<?php

namespace CreamIO\UserBundle\Controller;

use CreamIO\UserBundle\Entity\BUser;
use CreamIO\UserBundle\Exceptions\APIError;
use CreamIO\UserBundle\Service\APIService;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class BUserController.
 *
 * @todo Add routes for PUT method
 *
 * @Route("/admin/api", name="backoffice_api_")
 */
class BUserController extends Controller
{
    const ACCEPTED_CONTENT_TYPE = 'application/json';
    const LIST_RESULTS_FOR_IDENTIFIER = 'users-list';
    const LOGIN_RESULTS_FOR_IDENTIFIER = 'login';

    /**
     * User creation route.
     *
     * @Route("/users", name="user_post", methods="POST")
     *
     * @param Request            $request    Handled HTTP request
     * @param APIService         $APIService Autowired API service
     * @param ValidatorInterface $validator  Autowired Validator service
     *
     * @return Response
     */
    public function create(Request $request, APIService $APIService, ValidatorInterface $validator): Response
    {
        if (SELF::ACCEPTED_CONTENT_TYPE !== $request->headers->get('content_type')) {
            throw $APIService->error(Response::HTTP_BAD_REQUEST, APIError::INVALID_CONTENT_TYPE);
        }
        $datas = $request->getContent();
        $user = $APIService->generateAppUserFromJSON($datas);
        $validationErrors = $validator->validate($user);
        $user->eraseCredentials();
        if (count($validationErrors) > 0) {
            throw $APIService->postError($validationErrors);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $redirectionUrl = $this->generateUrl('backoffice_api_user_get', ['id' => $user->getId()]);

        return $APIService->successWithoutResultsRedirected($user->getId(), $request, Response::HTTP_CREATED, $redirectionUrl);
    }

    /**
     * User patch route.
     *
     * @Route("/users/{id}", name="user_patch", methods="PATCH")
     *
     * @param Request            $request    Handled HTTP request
     * @param string             $id         User id to patch
     * @param APIService         $APIService Autowired API service
     * @param ValidatorInterface $validator  Autowired Validator service
     *
     * @return Response
     */
    public function patch(Request $request, string $id, APIService $APIService, ValidatorInterface $validator): Response
    {
        if (SELF::ACCEPTED_CONTENT_TYPE !== $request->headers->get('content_type')) {
            throw $APIService->error(Response::HTTP_BAD_REQUEST, APIError::INVALID_CONTENT_TYPE);
        }
        if (!Uuid::isValid($id)) {
            throw $APIService->error(Response::HTTP_NOT_FOUND, APIError::INVALID_UUID_ERROR);
        }
        $user = $this->getDoctrine()->getManager()->getRepository(BUser::class)->find($id);
        if (null === $user) {
            throw $APIService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        $datas = $request->getContent();
        $user = $APIService->mergeEntityFromJSON($user, $datas);
        $validationErrors = $validator->validate($user);
        if (count($validationErrors) > 0) {
            throw $APIService->postError($validationErrors);
        }
        $user->eraseCredentials();
        $this->getDoctrine()->getManager()->flush();

        return $APIService->successWithoutResults($id, Response::HTTP_OK, $request);
    }

    /**
     * User Profile route.
     *
     * @Route("/users/{id}", name="user_get", methods="GET")
     *
     * @param Request    $request    The handled HTTP request
     * @param string     $id         User id to get information for
     * @param APIService $APIService Autowired API service
     *
     * @return JsonResponse
     */
    public function details(Request $request, string $id, APIService $APIService): Response
    {
        if (!Uuid::isValid($id)) {
            throw $APIService->error(Response::HTTP_NOT_FOUND, APIError::INVALID_UUID_ERROR);
        }
        $user = $this->getDoctrine()->getManager()->getRepository(BUser::class)->find($id);
        if (null === $user) {
            throw $APIService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }

        return $APIService->successWithResults(['user' => $user], Response::HTTP_OK, $user->getId(), $request);
    }

    /**
     * User deletion route.
     *
     * @Route("/users/{id}", name="user_delete", methods="DELETE")
     *
     * @param Request    $request    The handled HTTP request
     * @param string     $id         User id to delete
     * @param APIService $APIService Autowired API service
     *
     * @return JsonResponse
     */
    public function delete(Request $request, string $id, APIService $APIService): Response
    {
        if (!Uuid::isValid($id)) {
            throw $APIService->error(Response::HTTP_NOT_FOUND, APIError::INVALID_UUID_ERROR);
        }
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(BUser::class)->find($id);
        if (null === $user) {
            throw $APIService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        $em->remove($user);
        $em->flush();

        return $APIService->successWithoutResults($id, Response::HTTP_OK, $request);
    }

    /**
     * User Profiles list route.
     *
     * @Route("/users", name="user_list_get", methods="GET")
     *
     * @param Request    $request    Handled HTTP request
     * @param APIService $APIService Autowired API service
     *
     * @return JsonResponse
     */
    public function detailsList(Request $request, APIService $APIService): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(BUser::class);
        $usersList = $repo->findAll();

        return $APIService->successWithResults(['users' => $usersList], Response::HTTP_OK, SELF::LIST_RESULTS_FOR_IDENTIFIER, $request);
    }

    /**
     * Login route.
     *
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request    $request    Handled HTTP request
     * @param APIService $APIService Autowired API service
     *
     * @return JsonResponse
     */
    public function login(Request $request, APIService $APIService): Response
    {
        return $APIService->successWithoutResults(SELF::LOGIN_RESULTS_FOR_IDENTIFIER, Response::HTTP_OK, $request);
    }
}
