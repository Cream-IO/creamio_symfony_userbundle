<?php

namespace App\Entity;

use CreamIO\UserBundle\Entity\BUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreamIO\UserBundle\Repository\APITokenRepository")
 * @ORM\Table(name="creamio_api_token")
 */
class APIToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="hash", type="string", length=255)
     */
    private $hash;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="CreamIO\UserBundle\Entity\BUser")
     * @ORM\JoinColumn(name="related_user", nullable=false)
     */
    private $relatedUser;

    public function getId()
    {
        return $this->id;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRelatedUser(): ?BUser
    {
        return $this->relatedUser;
    }

    public function setRelatedUser(?BUser $relatedUser): self
    {
        $this->relatedUser = $relatedUser;

        return $this;
    }
}
