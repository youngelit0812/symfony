<?php

namespace App\Entity;

use App\Repository\UrlStoreRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UrlStoreRepository::class)
 * @ORM\Table(name="`url_store`")
 */
class Url_store
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $no;

    /**
     * @ORM\Column(type="string", length=253, unique=true)
     */
    private $i_dns;

    /**
     * @ORM\Column(type="string")
     */
    private $i_uri;
	
	/**
     * @ORM\Column(type="string")
     */
    private $i_parameter;

    /**
     * @ORM\Column(type="integer")
     */
    private $uid;

    public function getNo(): ?int
    {
        return $this->no;
    }

    public function getI_dns(): ?string
    {
        return $this->i_dns;
    }

    public function setI_dns(string $i_dns): self
    {
        $this->i_dns = $i_dns;

        return $this;
    }

    public function getI_uri(): string
    {
        return $this->i_uri;
    }

    public function setI_uri(string $i_uri): self
    {
        $this->i_uri = $i_uri;

        return $this;
    }

	public function getI_parameter(): ?string
    {
        return $this->i_parameter;
    }

    public function setI_parameter(string $i_parameter): self
    {
        $this->i_parameter = $i_parameter;
        return $this;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;
        return $this;
    }
}
