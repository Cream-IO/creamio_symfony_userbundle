<?php

namespace CreamIO\UserBundle\Service;

use CreamIO\UserBundle\Entity\BUser;
use GBProd\UuidNormalizer\UuidNormalizer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
     * APIService constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder Autowired UserPasswordEncoder service
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
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
        $encoders = [new JsonEncoder()];
        $objectNormalizer = new ObjectNormalizer();
        $objectNormalizer->setIgnoredAttributes(['password', 'salt', 'passwordLegal', 'plainPassword']);
        $normalizers = [new DateTimeNormalizer('d-m-Y H:i:s', new \DateTimeZone('Europe/Paris')), $objectNormalizer, new UuidNormalizer()];

        return new Serializer($normalizers, $encoders);
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
