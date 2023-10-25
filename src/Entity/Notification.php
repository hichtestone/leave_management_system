<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    public const NOTIF_CLIENT_ADD = 1;
    public const NOTIF_CLIENT_EDIT = 2;
    public const NOTIF_ADMIN_PROGRESS = 3;
    public const NOTIF_ADMIN_ACCEPTED = 4 ;
    public const NOTIF_ADMIN_REFUSED = 5;
    public const NOTIF_ADMIN_ADVANCED= 6;
    public const  NOTIF_CLIENT_ADVANCED= 7;
    public const NOTIF_ADMIN_ADMINISTRATIVE= 8;
    public const  NOTIF_CLIENT_ADMINISTRATIVE= 9;
    public const  NOTIF = [
        self::NOTIF_CLIENT_ADD => 'Nouvelle demande a été effectuée',
        self::NOTIF_CLIENT_EDIT => 'Une demande a été mofifier',
        self::NOTIF_ADMIN_PROGRESS => 'Votre demande est en cours du traitement',
        self::NOTIF_ADMIN_ACCEPTED => 'Votre demande a été validée',
        self::NOTIF_ADMIN_REFUSED => 'Votre demande a été refusée, merci de voir le motif de refus',
        self::NOTIF_ADMIN_ADVANCED => 'Une nouvelle demande d\'avance a été deposée',
        self::NOTIF_CLIENT_ADVANCED => 'Votre demande d\'avance est acceptée',
        self::NOTIF_ADMIN_ADMINISTRATIVE => 'Une nouvelle demande d\'attestation a été deposée',
        self::NOTIF_CLIENT_ADMINISTRATIVE => 'Votre demande d\'attestation est acceptée'
    ];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isRead;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $readAt;

    /**
     * @ORM\ManyToOne(targetEntity=Demand::class, inversedBy="notifications")
     */
    private $demand;

    /**
     * @ORM\ManyToOne(targetEntity=Advance::class, inversedBy="notifications")
     */
    private $advance;

    /**
     * @ORM\ManyToOne(targetEntity=AdministrativeDemand::class, inversedBy="notifications")
     */
    private $administrativeDemand;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        if (!array_key_exists($message, self::NOTIF)&& $message !==null){
            throw new \Exception('le message' . $message . 'n\'existe pas dans la liste');
        }
        $this->message = $message;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): self
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function getDemand(): ?Demand
    {
        return $this->demand;
    }

    public function setDemand(?Demand $demand): self
    {
        $this->demand = $demand;

        return $this;
    }

    public function __toString(): string
    {
       return $this->getMessage();
    }

    public function getAdvance(): ?Advance
    {
        return $this->advance;
    }

    public function setAdvance(?Advance $advance): self
    {
        $this->advance = $advance;

        return $this;
    }

    public function getAdministrativeDemand(): ?AdministrativeDemand
    {
        return $this->administrativeDemand;
    }

    public function setAdministrativeDemand(?AdministrativeDemand $administrativeDemand): self
    {
        $this->administrativeDemand = $administrativeDemand;

        return $this;
    }


}
