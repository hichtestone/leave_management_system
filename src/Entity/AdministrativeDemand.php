<?php

namespace App\Entity;

use App\Repository\AdministrativeDemandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AdministrativeDemandRepository::class)
 */
class AdministrativeDemand
{
    public const STATUS_SENT = 1;
    public const STATUS_DONE = 2;

    public const STATUS= [
        self::STATUS_SENT => 'Nouveau',
        self::STATUS_DONE => 'Accusé',

    ];

    public const TYPE_TRAVAIL = 1;
    public const TYPE_TITULARISATION = 2;
    public const TYPE_SALAIRE = 3;

    public const TYPE= [
        self::TYPE_TRAVAIL => 'Attestaion de travail',
        self::TYPE_TITULARISATION => 'Attestation de titularisation',
        self::TYPE_SALAIRE => 'Attestation de salaire',

    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="administrativeDemands")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $filingAt;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="administrativeDemand")
     */
    private $notifications;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $responseDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $observation;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @throws \Exception
     */
    public function setStatus($status): void
    {
        if (!array_key_exists($status, self::STATUS) && $status !==null){
            throw new \Exception('le status '.$status.' non authorisé !');
        }
        $this->status = $status;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @throws \Exception
     */
    public function setType($type): void
    {
        if (!array_key_exists($type, self::TYPE) && $type !==null){
            throw new \Exception('le type '.$type.' non authorisé !');
        }
        $this->type = $type;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFilingAt(): ?\DateTimeInterface
    {
        return $this->filingAt;
    }

    public function setFilingAt(\DateTimeInterface $filingAt): self
    {
        $this->filingAt = $filingAt;

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setAdministrativeDemand($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getAdministrativeDemand() === $this) {
                $notification->setAdministrativeDemand(null);
            }
        }

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getResponseDate(): ?\DateTimeInterface
    {
        return $this->responseDate;
    }

    public function setResponseDate(?\DateTimeInterface $responseDate): self
    {
        $this->responseDate = $responseDate;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(string $observation): self
    {
        $this->observation = $observation;

        return $this;
    }
}
