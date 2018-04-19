<?php
/**
 * Back Office user entity.
 *
 * Main user entity used to authenticate to the backoffice application.
 *
 * @see      https://github.com/QRaimbault/base_symfony_admin_template
 *
 * @author    Quentin Raimbault <quentin.raimbault@gmail.com>
 * @copyright Copyright (c) 2018 Quentin Raimbault <quentin.raimbault@gmail.com>
 * @license   https://github.com/QRaimbault/base_symfony_admin_template/blob/master/LICENSE MIT
 */

namespace CreamIO\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_buser")
 * @ORM\Entity(repositoryClass="CreamIO\UserBundle\Repository\BUserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class BUser implements UserInterface, \Serializable
{
    /**
     * User's main ID.
     *
     * @var Uuid
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * Username.
     *
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=25, unique=true)
     *
     * @Assert\NotBlank(message="Username can't be blank")
     * @Assert\Length(min="3", max="30", minMessage="Username's lenght must be at least {{ limit }} characters", maxMessage="Username's lenght can't be above {{ limit }} characters")
     */
    private $username;

    /**
     * User's encrypted password.
     *
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;

    /**
     * User's plain password (not stored in database, only used at registration).
     *
     * @var string
     *
     * @Assert\NotBlank(message="Password can't be blank")
     * @Assert\Length(min="5", max="4096", minMessage="Password's lenght must be at least {{ limit }} characters", maxMessage="Password's lenght can't be above {{ limit }} characters")
     */
    private $plainPassword;

    /**
     * User's email address.
     *
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=190, unique=true)
     *
     * @Assert\NotBlank(message="Email can't be blank")
     * @Assert\Email()
     */
    private $email;

    /**
     * User's list of roles (used to handle permissions).
     *
     * @var string[]
     *
     * @ORM\Column(name="role", type="array")
     */
    private $roles;

    /**
     * User's first name.
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=80)
     *
     * @Assert\NotBlank(message="Firstname can't be blank")
     * @Assert\Length(min="2", max="80", minMessage="Firstname's lenght must be at least {{ limit }} characters", maxMessage="Firstname's lenght can't be above {{ limit }} characters")
     */
    private $firstName;

    /**
     * User's last name.
     *
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=80)
     *
     * @Assert\NotBlank(message="Lastname can't be blank")
     * @Assert\Length(min="2", max="80", minMessage="Lastname's lenght must be at least {{ limit }} characters", maxMessage="Lastname's lenght can't be above {{ limit }} characters")
     */
    private $lastName;

    /**
     * User's job.
     *
     * @var string
     *
     * @ORM\Column(name="job", type="string", length=160, nullable=true)
     * @Assert\Length(max="160", maxMessage="Job's lenght can't be above {{ limit }} characters")
     */
    private $job;

    /**
     * User's description.
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\Length(max="4000", maxMessage="Description's lenght can't be above {{ limit }} characters")
     */
    private $description;

    /**
     * User creation time.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="creation_time", type="datetime")
     */
    private $creationTime;

    /**
     * BackOfficeUser constructor.
     */
    public function __construct()
    {
        $this->creationTime = new \DateTime();
    }

    /**
     * ID Getter.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Salt getter.
     *
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Password getter.
     *
     * @codeCoverageIgnore
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Password setter.
     *
     * @param string $password
     *
     * @return BUser
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Roles getter.
     *
     * @return array|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * Roles setter.
     *
     * @param string[] $roles
     *
     * @return BUser
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Email getter.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Email setter.
     *
     * @param string $email
     *
     * @return BUser
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * FirstName getter.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * FirstName setter.
     *
     * @param string $firstName
     *
     * @return BUser
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * LastName getter.
     *
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * LastName getter.
     *
     * @param string $lastName
     *
     * @return BUser
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Job getter.
     *
     * @return string
     */
    public function getJob(): ?string
    {
        return $this->job;
    }

    /**
     * Job setter.
     *
     * @param string $job
     *
     * @return BUser
     */
    public function setJob(string $job): self
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Description getter.
     *
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return BUser
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Checks if user pasword equals to username, password constraint.
     *
     * @Assert\IsTrue(message="Password can't be the equal to username")
     *
     * @return bool
     */
    public function isPasswordLegal(): bool
    {
        return $this->getPlainPassword() !== $this->getUsername();
    }

    /**
     * PlainPassword getter.
     *
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * PlainPassword setter.
     *
     * @param string $plainPassword
     *
     * @return BUser
     */
    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Username getter.
     *
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Username setter.
     *
     * @param string $username
     *
     * @return BUser
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Creation time getter.
     *
     * @return \DateTime
     */
    public function getCreationTime(): \DateTime
    {
        return $this->creationTime;
    }

    /**
     * @param \DateTime $creationTime
     *
     * @return BUser
     */
    public function setCreationTime(\DateTime $creationTime): self
    {
        $this->creationTime = $creationTime;

        return $this;
    }

    /**
     * Removes plain password after registration.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Serialization implementation.
     *
     * @codeCoverageIgnore
     *
     * @see \Serializable::serialize()
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
        ]);
    }

    /**
     * Serialization implementation.
     *
     * @codeCoverageIgnore
     *
     * @see \Serializable::unserialize()
     *
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        list(
            $this->id,
            $this->username,
            $this->password
            ) = unserialize($serialized);
    }

    /**
     * Magic __toString method.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->username;
    }
}
