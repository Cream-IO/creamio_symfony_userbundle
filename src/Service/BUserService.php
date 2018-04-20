<?php

namespace CreamIO\UserBundle\Service;

use CreamIO\UserBundle\Entity\BUser;
use CreamIO\BaseBundle\Exceptions\APIError;
use CreamIO\BaseBundle\Exceptions\APIException;
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
class BUserService
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
}
