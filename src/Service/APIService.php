<?php

namespace CreamIO\UserBundle\Service;

use CreamIO\UserBundle\Entity\BUser;
use CreamIO\UserBundle\Exceptions\APIError;
use CreamIO\UserBundle\Exceptions\APIException;
use Doctrine\ORM\EntityManagerInterface;
use GBProd\UuidNormalizer\UuidNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Handles all logical operations related to the API activites.
 *
 * Operated mainly on serialization/deserialization, JSON and API response
 *
 * @todo Add methods for PUT
 */
class APIService
{
    /**
     * @var UserPasswordEncoderInterface Autowired UserPasswordEncoder service
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface Autowired Doctrine service
     */
    private $em;

    /**
     * APIService constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder Autowired UserPasswordEncoder service
     * @param EntityManagerInterface       $entityManager   Autowired Doctrine service
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $entityManager;
    }

    /**
     * Serializer generator.
     *
     * Allows to generate a serializer already including the UUIDSerializerHandler in the registered handlers
     *
     * @return Serializer Serializer instance
     */
    public function generateSerializer(): Serializer
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $objectNormalizer = new ObjectNormalizer();
        $objectNormalizer->setIgnoredAttributes(['password', 'salt', 'passwordLegal', 'plainPassword']);
        $normalizers = [new DateTimeNormalizer('d-m-Y H:i:s', new \DateTimeZone('Europe/Paris')), $objectNormalizer, new UuidNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer;
    }

    /**
     * Deserialize JSON encoded datas to AppUser entity instance.
     *
     * @param string $datas JSON Serialized AppUser entity
     *
     * @return BUser
     */
    public function generateAppUserFromJSON(string $datas): BUser
    {
        $user = $this->generateSerializer()->deserialize($datas, BUser::class, 'json');
        /** @var BUser $user */
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        $user->setCreationTime(new \DateTime());

        return $user;
    }

    public function mergeEntityFromJSON(BUser $user, string $datas): BUser
    {
        $this->generateSerializer()->deserialize($datas, BUser::class, 'json', ['object_to_populate' => $user]);
        if (null !== $user->getPlainPassword()) {
            $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
        } else {
            /* @todo Plain password placeholder is pretty shitty, find a better way to handle validation passing when password is not updated */
            $user->setPlainPassword('Placeholder');
        }

        return $user;
    }

    /**
     * Generates a JSONResponse for errored requests.
     *
     * This method is used for every methods since it handles all kind of errors
     *
     * @param int    $responseCode Response code to send (Constants defined in Response::class)
     * @param string $reason       Error reason to return
     *
     * @return APIException
     */
    public function error(int $responseCode, string $reason): APIException
    {
        $APIError = new APIError($responseCode, $reason);

        return new APIException($APIError);
    }

    public function postError(ConstraintViolationListInterface $validationErrors): APIException
    {
        $errors = [];
        foreach ($validationErrors as $error) {
            /* @var ConstraintViolation $error */
            $errors[$error->getPropertyPath()] = $error->getMessage();
        }

        $APIError = new APIError(Response::HTTP_BAD_REQUEST, APIError::VALIDATION_ERROR);
        $APIError->set('fields-validation-violations', $errors);

        return new APIException($APIError);
    }

    /**
     * Generates a JSONResponse for successful requests requiring results.
     *
     * This method is used for GET method since it required to return results
     *
     * @param mixed   $results    Datas to send as results, can be array of objects or a single object
     * @param int     $statusCode Response code to send (Constants defined in Response::class)
     * @param string  $resultsFor ID of the requested object, or identifier for collection requests
     * @param Request $request    Handled HTTP request to get method from
     *
     * @return JsonResponse
     */
    public function successWithResults($results, int $statusCode, string $resultsFor, Request $request): JsonResponse
    {
        $return = [
            'status' => 'success',
            'code' => $statusCode,
            'request-method' => $request->getMethod(),
            'results-for' => $resultsFor,
            'results' => $results,
        ];
        $serializedReturn = $this->generateSerializer()->serialize($return, 'json');

        return new JsonResponse($serializedReturn, $statusCode, [], true);
    }

    /**
     * Generates a JSONResponse for successful requests not requiring any results.
     *
     * This method is used for DELETE, POST, PATCH and PUT methods since they do not need any result to return
     *
     * @param string  $id         Ressource ID
     * @param int     $statusCode Response code to send (Constants defined in Response::class)
     * @param Request $request    Handled HTTP request to get method from
     *
     * @return JsonResponse
     */
    public function successWithoutResults(string $id, int $statusCode, Request $request): JsonResponse
    {
        $return = [
            'status' => 'success',
            'code' => $statusCode,
            'request-method' => $request->getMethod(),
            'request-ressource-id' => $id,
        ];
        $serializedReturn = $this->generateSerializer()->serialize($return, 'json');

        return new JsonResponse($serializedReturn, $statusCode, [], true);
    }

    /**
     * Generates a JSONResponse for successful requests not requiring any results but redirecting.
     *
     * This method is used to handle ressource creation requests since those requests generates a Location header redirecting to the created ressource get URL
     *
     * @param string  $id             Ressource ID
     * @param Request $request        Handled HTTP request to get method from
     * @param int     $statusCode     Response code to send (Constants defined in Response::class)
     * @param string  $redirectionURL Location header URL
     *
     * @return JsonResponse Success response
     */
    public function successWithoutResultsRedirected(string $id, Request $request, int $statusCode, string $redirectionURL): JsonResponse
    {
        $return = [
            'status' => 'success',
            'code' => $statusCode,
            'request-method' => $request->getMethod(),
            'request-ressource-id' => $id,
        ];
        $serializedReturn = $this->generateSerializer()->serialize($return, 'json');

        return new JsonResponse($serializedReturn, Response::HTTP_CREATED, ['Location' => $redirectionURL], true);
    }
}
