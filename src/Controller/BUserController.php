<?php

namespace CreamIO\UserBundle\Controller;

use CreamIO\UserBundle\Entity\APIToken;
use CreamIO\BaseBundle\Exceptions\APIError;
use CreamIO\BaseBundle\Exceptions\APIException;
use CreamIO\BaseBundle\Service\APIService;
use CreamIO\UserBundle\Entity\BUser;
use CreamIO\UserBundle\Service\BUserService;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class BUserController.
 *
 * @todo Add routes for PUT method
 *
 * @Route("/admin/api", name="backoffice_api_")
 */
class BUserController extends Controller
{
    private const ACCEPTED_CONTENT_TYPE = 'application/json';
    private const LIST_RESULTS_FOR_IDENTIFIER = 'users-list';
    private const LOGIN_RESULTS_FOR_IDENTIFIER = 'login';

    /**
     * @var APIService Injected API service
     */
    private $apiService;

    /**
     * @var BUserService Injected user service
     */
    private $userService;

    /**
     * @var Serializer Generated serializer from constructor
     */
    private $serializer;

    /**
     * @var UserPasswordEncoderInterface Injected password encoder service
     */
    private $passwordEncoder;

    /**
     * BUserController constructor.
     *
     * @param APIService                   $APIService      Injected API service
     * @param BUserService                 $userService     Injected User service
     * @param UserPasswordEncoderInterface $passwordEncoder Inject password encoder service
     */
    public function __construct(APIService $APIService, BUserService $userService, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->apiService = $APIService;
        $this->userService = $userService;
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $userService->generateSerializer();
    }

    /**
     * User creation route.
     *
     * @Route("/users", name="user_post", methods="POST")
     *
     * @param Request $request Handled HTTP request
     *
     * @throws \LogicException
     * @throws APIException
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        if (self::ACCEPTED_CONTENT_TYPE !== $request->headers->get('content_type')) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, APIError::INVALID_CONTENT_TYPE);
        }
        $datas = $request->getContent();
        $user = $this->userService->generateAppUserFromJSON($datas);
        $this->apiService->validateEntity($user);
        $user->eraseCredentials();
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $redirectionUrl = $this->generateUrl('cream_io_user.details', ['id' => $user->getId()]);

        return $this->apiService->successWithoutResultsRedirected($user->getId(), $request, Response::HTTP_CREATED, $redirectionUrl);
    }

    /**
     * User patch route.
     *
     * @Route("/users/{id}", name="user_patch", methods="PATCH")
     *
     * @param Request $request Handled HTTP request
     * @param string  $id      User id to patch
     *
     * @throws \LogicException
     * @throws APIException
     *
     * @return JsonResponse
     */
    public function patch(Request $request, string $id): JsonResponse
    {
        if (self::ACCEPTED_CONTENT_TYPE !== $request->headers->get('content_type')) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, APIError::INVALID_CONTENT_TYPE);
        }
        if (!Uuid::isValid($id)) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::INVALID_UUID_ERROR);
        }
        $user = $this->getDoctrine()->getManager()->getRepository(BUser::class)->find($id);
        if (null === $user) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        $datas = $request->getContent();
        $user = $this->userService->mergeEntityFromJSON($user, $datas);
        $this->apiService->validateEntity($user);
        $user->eraseCredentials();
        $this->getDoctrine()->getManager()->flush();

        return $this->apiService->successWithoutResults($id, Response::HTTP_OK, $request);
    }

    /**
     * User Profile route.
     *
     * @Route("/users/{id}", name="user_get", methods="GET")
     *
     * @param Request $request The handled HTTP request
     * @param string  $id      User id to get information for
     *
     * @throws \LogicException
     * @throws APIException
     *
     * @return JsonResponse
     */
    public function details(Request $request, string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::INVALID_UUID_ERROR);
        }
        $user = $this->getDoctrine()->getManager()->getRepository(BUser::class)->find($id);
        if (null === $user) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }

        return $this->apiService->successWithResults(['user' => $user], Response::HTTP_OK, $user->getId(), $request, $this->serializer);
    }

    /**
     * User deletion route.
     *
     * @Route("/users/{id}", name="user_delete", methods="DELETE")
     *
     * @param Request $request The handled HTTP request
     * @param string  $id      User id to delete
     *
     * @throws \LogicException
     * @throws APIException
     *
     * @return JsonResponse
     */
    public function delete(Request $request, string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::INVALID_UUID_ERROR);
        }
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(BUser::class)->find($id);
        if (null === $user) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        $em->remove($user);
        $em->flush();

        return $this->apiService->successWithoutResults($id, Response::HTTP_OK, $request);
    }

    /**
     * User Profiles list route.
     *
     * @Route("/users", name="user_list_get", methods="GET")
     *
     * @param Request $request Handled HTTP request
     *
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function detailsList(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(BUser::class);
        $usersList = $repo->findAll();

        return $this->apiService->successWithResults(['users' => $usersList], Response::HTTP_OK, self::LIST_RESULTS_FOR_IDENTIFIER, $request, $this->serializer);
    }

    /**
     * Login route.
     *
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request Handled HTTP request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $receivedContent = \json_decode($request->getContent(), true);
        if (null === $receivedContent) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, 'Bad request content.', ['technical' => 'Tried to login with empty content.']);
        }
        if ((false === \array_key_exists('username', $receivedContent)) || (false === \array_key_exists('password', $receivedContent))) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, 'Bad request content.', ['technical' => 'Tried to login with missing username or password.']);
        }
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(BUser::class);
        $user = $userRepo->findOneByUsername($receivedContent['username']);
        if (null === $user) {
            throw $this->apiService->error(Response::HTTP_UNAUTHORIZED, 'Bad credentials.', ['technical' => sprintf('Tried to login with invalid username : %s.', $receivedContent['username'])]);
        }
        $isPasswordValid = $this->passwordEncoder->isPasswordValid($user, $receivedContent['password']);
        if (!$isPasswordValid) {
            throw $this->apiService->error(Response::HTTP_UNAUTHORIZED, 'Bad credentials.', ['technical' => 'Tried to login with invalid password.']);
        }
        $apiToken = new APIToken();
        $apiToken->setHash(base64_encode(random_bytes(50)));
        $apiToken->setCreatedAt(new \DateTime('now'));
        $apiToken->setRelatedUser($user);
        $em->persist($apiToken);
        $em->flush();

        return $this->apiService->successWithResults(['token' => $apiToken->getHash()], Response::HTTP_OK, self::LOGIN_RESULTS_FOR_IDENTIFIER, $request, $this->serializer);
    }
}
