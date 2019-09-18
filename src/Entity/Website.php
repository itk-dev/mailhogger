<?php

/*
 * This file is part of rimi-itk/mailhogger.
 *
 * (c) 2018–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MailHogger.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\WebsiteRepository")
 */
class Website
{
    /**
     * @var string
     *
     * @ORM\Column(type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $senderEmails;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoUrl;

    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Website
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set senderEmails.
     *
     * @param array $senderEmails
     *
     * @return Website
     */
    public function setSenderEmails($senderEmails): self
    {
        $this->senderEmails = $senderEmails;

        return $this;
    }

    /**
     * Get senderEmails.
     *
     * @return array
     */
    public function getSenderEmails(): array
    {
        return $this->senderEmails;
    }

    /**
     * @return string
     */
    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    /**
     * @param string $logoUrl
     */
    public function setLogoUrl($logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }
}
