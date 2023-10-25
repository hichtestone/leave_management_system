<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Vich\Uploadable
 */
class User implements UserInterface, \Serializable
{
    public const CIVILITY_MR = 1;
    public const CIVILITY_MS = 2;
    public const CIVILITY_DR = 3;
    public const CIVILITY = [
        self::CIVILITY_MR => 'Mr',
        self::CIVILITY_MS => 'Ms',
        self::CIVILITY_DR => 'Dr',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(message="Le format de l'adresse email doit être valide")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\Length(max=4096)
     */
    private $plainedPassword;

    /**
     * @var File
     * @Vich\UploadableField(mapping="user_image", fileNameProperty="filename")
     * @Assert\File(
     *     maxSize = "1024k",
     *     mimeTypes = {"image/png", "image/jpeg"},
     *     mimeTypesMessage = "Merci de choisir un type adequate pour l'image"
     * )
     */
    private $imageFile;

    /**
     * @ORM\Column (type="string", length=255, nullable=true)
     * @var string|null
     */
    private $filename;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $civility;

    /**
     * @ORM\Column(type="float",nullable=true)
     */
    private $solde;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min=3, minMessage="Le nom prénom de l'userProject projet doit faire entre 3 et 255 caractères", max=255, maxMessage="Le prénom de l'userProject projet doit faire entre 3 et 255 caractères")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adress;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nationality;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @var bool|null
     */
    private $isSuperAdmin;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="users")
     */
    private $RoleRole;

    /**
     * @ORM\ManyToOne(targetEntity=Department::class, inversedBy="users")
     * @var Department|null
     */
    private $department;


    /**
     * @ORM\OneToMany(targetEntity=Demand::class, mappedBy="user")
     */
    private $demands;

    /**
     * @ORM\OneToOne(targetEntity=Contrat::class, cascade={"persist", "remove"})
     */
    private $contrat;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="responsable")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="user")
     */
    private $responsable;

    /**
     * @ORM\OneToMany(targetEntity=Advance::class, mappedBy="user")
     */
    private $advances;

    /**
     * @ORM\OneToMany(targetEntity=AdministrativeDemand::class, mappedBy="user")
     */
    private $administrativeDemands;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\ManyToMany(targetEntity=Company::class, mappedBy="users")
     */
    private $companies;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $signature;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }


    public function getSecurity(): array
    {
        $roles = [];
        foreach ($this->RoleRole as $role) {
            $roles[] = $role->getCode();
        }

        return array_unique($roles);
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->getSecurity();
        if ($this->isSuperAdmin) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoleRole(): Collection
    {
        return $this->RoleRole;
    }

    public function addRoleRole(Role $roleRole): self
    {
        if (!$this->RoleRole->contains($roleRole)) {
            $this->RoleRole[] = $roleRole;
        }

        return $this;
    }

    public function removeRoleRole(Role $roleRole): self
    {
        $this->RoleRole->removeElement($roleRole);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainedPassword()
    {
        return $this->plainedPassword;
    }

    /**
     * @param mixed $plainedPassword
     */
    public function setPlainedPassword($plainedPassword): void
    {
        $this->plainedPassword = $plainedPassword;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getCivility()
    {
        return $this->civility;
    }

    /**
     * @param mixed $civility
     */
    public function setCivility($civility): void
    {
        if (!array_key_exists($civility, self::CIVILITY) && $civility !== null) {
            throw new \Exception('the grade ' . $civility . ' is not authorize !');
        }

        $this->civility = $civility;
    }

    /**
     * @return mixed
     */
    public function getSolde()
    {
        return $this->solde;
    }

    /**
     * @param mixed $solde
     */
    public function setSolde($solde): void
    {
        $this->solde = $solde;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * @param mixed $adress
     */
    public function setAdress($adress): void
    {
        $this->adress = $adress;
    }

    /**
     * @return mixed
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param mixed $nationality
     */
    public function setNationality($nationality): void
    {
        $this->nationality = $nationality;
    }

    /**
     * @return mixed
     */
    public function getIsSuperAdmin(): ?bool
    {
        return $this->isSuperAdmin;
    }

    /**
     * @return mixed
     */
    public function setIsSuperAdmin(?bool $isSuperAdmin): self
    {
        $this->isSuperAdmin = $isSuperAdmin;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): User
    {
        $this->imageFile = $imageFile;
        if ($this->imageFile instanceof UploadedFile) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }

    public function __construct()
    {
        $this->RoleRole = new ArrayCollection();
        $this->demands = new ArrayCollection();
        $this->responsable = new ArrayCollection();
        $this->advances = new ArrayCollection();
        $this->administrativeDemands = new ArrayCollection();
        $this->companies = new ArrayCollection();
    }

    public function __toString()
    {
        return '';
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department =null): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection|Demand[]
     */
    public function getDemands(): Collection
    {
        return $this->demands;
    }

    public function addDemand(Demand $demand): self
    {
        if (!$this->demands->contains($demand)) {
            $this->demands[] = $demand;
            $demand->setUser($this);
        }

        return $this;
    }

    public function removeDemand(Demand $demand): self
    {
        if ($this->demands->removeElement($demand)) {
            // set the owning side to null (unless already changed)
            if ($demand->getUser() === $this) {
                $demand->setUser(null);
            }
        }

        return $this;
    }

    public function getContrat(): ?Contrat
    {
        return $this->contrat;
    }

    public function setContrat(?Contrat $contrat): self
    {
        $this->contrat = $contrat;

        return $this;
    }

    public function getUser(): ?self
    {
        return $this->user;
    }

    public function setUser(?self $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getResponsable(): Collection
    {
        return $this->responsable;
    }

    public function addResponsable(self $responsable): self
    {
        if (!$this->responsable->contains($responsable)) {
            $this->responsable[] = $responsable;
            $responsable->setUser($this);
        }

        return $this;
    }

    public function removeResponsable(self $responsable): self
    {
        if ($this->responsable->removeElement($responsable)) {
            // set the owning side to null (unless already changed)
            if ($responsable->getUser() === $this) {
                $responsable->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }


    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->password,
            ) = unserialize($serialized);
    }

    /**
     * @return Collection|Advance[]
     */
    public function getAdvances(): Collection
    {
        return $this->advances;
    }

    public function addAdvance(Advance $advance): self
    {
        if (!$this->advances->contains($advance)) {
            $this->advances[] = $advance;
            $advance->setUser($this);
        }

        return $this;
    }

    public function removeAdvance(Advance $advance): self
    {
        if ($this->advances->removeElement($advance)) {
            // set the owning side to null (unless already changed)
            if ($advance->getUser() === $this) {
                $advance->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AdministrativeDemand[]
     */
    public function getAdministrativeDemands(): Collection
    {
        return $this->administrativeDemands;
    }

    public function addAdministrativeDemand(AdministrativeDemand $administrativeDemand): self
    {
        if (!$this->administrativeDemands->contains($administrativeDemand)) {
            $this->administrativeDemands[] = $administrativeDemand;
            $administrativeDemand->setUser($this);
        }

        return $this;
    }

    public function removeAdministrativeDemand(AdministrativeDemand $administrativeDemand): self
    {
        if ($this->administrativeDemands->removeElement($administrativeDemand)) {
            // set the owning side to null (unless already changed)
            if ($administrativeDemand->getUser() === $this) {
                $administrativeDemand->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->addUser($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->removeElement($company)) {
            $company->removeUser($this);
        }

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }


}
