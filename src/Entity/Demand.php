<?php

namespace App\Entity;

use App\Repository\DemandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use mysql_xdevapi\Exception;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass=DemandRepository::class)
 */
class Demand
{
    public const ERMERGENCY_HIGHT = 1;
    public const ERMERGENCY_MEDIUM = 2 ;
    public const ERMERGENCY_LOW = 3 ;
    public const  ERMERGENCY = [
        self::ERMERGENCY_HIGHT => 'Haute',
        self::ERMERGENCY_MEDIUM => 'Moyenne',
        self::ERMERGENCY_LOW => 'Faible',
    ];
    public const STATUS_SENT = 1;
    public const STATUS_IN_PROGRESS = 2;
    public  const STATUS_DONE= 3;
    public const  STATUS_REFUSE= 4;
    public const  STATUS_ANNULE= 5;
    public const STATUS= [
        self::STATUS_SENT => 'Nouveau',
        self::STATUS_IN_PROGRESS => 'En cours du traitement',
        self::STATUS_DONE => 'Validé',
        self::STATUS_REFUSE => 'Refusé',
        self::STATUS_ANNULE => 'Annulé',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
//
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     * @Assert\Expression(
     *     "this.getStartDate() < this.getEndDate()",
     *     message="La date fin ne doit pas être antérieure à la date début")
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reason;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $filingDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="demands")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emergency;

    /**
     * @ORM\OneToMany(targetEntity=SupportingDocument::class, mappedBy="demand", cascade={"persist"})
     */
    private $supportingDocuments;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isCancled;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cause;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="demand")
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

    public function __construct()
    {
        $this->supportingDocuments = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
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


    public function getFilingDate(): ?\DateTimeInterface
    {
        return $this->filingDate;
    }

    public function setFilingDate(?\DateTimeInterface $filingDate): self
    {
        $this->filingDate = $filingDate;

        return $this;
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

    public function getEmergency(): ?string
    {
        return $this->emergency;
    }

    /**
     * @param mixed $emergency
     * @throws \Exception
     */
    public function setEmergency($emergency): void
    {
        if (!array_key_exists($emergency, self::ERMERGENCY)){
            throw new \Exception('le niveau d\'urgence' . $emergency . 'non autorisé');
        }
        $this->emergency = $emergency;

    }

    /**
     * @return Collection|SupportingDocument[]
     */
    public function getSupportingDocuments(): Collection
    {
        return $this->supportingDocuments;
    }

    public function addSupportingDocument(SupportingDocument $supportingDocument): self
    {
        if (!$this->supportingDocuments->contains($supportingDocument)) {
            $this->supportingDocuments[] = $supportingDocument;
            $supportingDocument->setDemand($this);
        }

        return $this;
    }

    public function removeSupportingDocument(SupportingDocument $supportingDocument): self
    {
        if ($this->supportingDocuments->removeElement($supportingDocument)) {
            // set the owning side to null (unless already changed)
            if ($supportingDocument->getDemand() === $this) {
                $supportingDocument->setDemand(null);
            }
        }

        return $this;
    }

    public function getIsCancled(): ?bool
    {
        return $this->isCancled;
    }

    public function setIsCancled(?bool $isCancled): self
    {
        $this->isCancled = $isCancled;

        return $this;
    }

    public function getCause(): ?string
    {
        return $this->cause;
    }

    public function setCause(?string $cause): self
    {
        $this->cause = $cause;

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
            $notification->setDemand($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getDemand() === $this) {
                $notification->setDemand(null);
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
}
