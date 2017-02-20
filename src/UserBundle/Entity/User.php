<?php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{

    const USER_DOCTOR = 0;
    const USER_PATIENT = 1;
    const USER_MALE = 2;
    const USER_FEMALE = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @Groups({"simple"})
     */
    protected $id;

    /**
    * @Expose
    * @ORM\Column(name="firstname", type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $firstname;

    /**
    * @Expose
    * @ORM\Column(name="lastname", type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $lastname;

    /**
    * @Expose
    * @ORM\Column(name="expertise", type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $expertise;

    /**
    * @Expose
    * @ORM\Column(name="maidenname", type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $maidenname;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $ipp;

    /**
    * @Expose
    * @ORM\Column(type="smallint")
    * @Groups({"simple"})
    */
    protected $type;

    /**
    * @Expose
    * @ORM\Column(name="folder", type="string", unique=true)
    * @Groups({"simple"})
    */
    protected $folder;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\Organization", inversedBy="users")
    * @Groups({"simple"})
    */
    protected $organization;

    /**
    * @ORM\OneToOne(targetEntity="UserBundle\Entity\Invite", mappedBy="user")
    */
    protected $invite;

    /**
    * @ORM\OneToMany(targetEntity="UserBundle\Entity\Invite", mappedBy="fromUser")
    */
    protected $invites;

    /**
    * @ORM\OneToMany(targetEntity="UserBundle\Entity\DoctorPatient", mappedBy="doctor")
    */
    protected $doctorPatients;

    /**
    * @ORM\OneToMany(targetEntity="UserBundle\Entity\DoctorPatient", mappedBy="patient")
    */
    protected $patientDoctors;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="fromUser")
    * @ORM\OrderBy({"id" = "DESC"})
    */
    protected $sentMessages;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="toUser")
    * @ORM\OrderBy({"id" = "DESC"})
    */
    protected $receivedMessages;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Category", mappedBy="user")
    */
    protected $categories;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Listing", mappedBy="owner")
    */
    protected $listings;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserListing", mappedBy="patient")
    */
    protected $userListings;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\ItemActivity", mappedBy="user")
    */
    protected $itemActivities;

    /**
    * @Expose
    * @ORM\Column(type="integer", nullable=true)
    * @Groups({"simple"})
    */
    protected $birthdayDay;

    /**
    * @Expose
    * @ORM\Column(type="integer", nullable=true)
    * @Groups({"simple"})
    */
    protected $birthdayMonth;

    /**
    * @Expose
    * @ORM\Column(type="integer", nullable=true)
    * @Groups({"simple"})
    */
    protected $birthdayYear;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $phoneNumber;

    /**
    * @Expose
    * @ORM\Column(type="float", nullable=true)
    * @Groups({"simple"})
    */
    protected $weight;

    /**
    * @Expose
    * @ORM\Column(type="float", nullable=true)
    * @Groups({"simple"})
    */
    protected $height;

    /**
    * @Expose
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"simple"})
    */
    protected $smoker;

    /**
    * @Expose
    * @ORM\Column(type="smallint", length=1, nullable=true)
    * @Groups({"simple"})
    */
    protected $gender;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $address;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $addressMore;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $addressMore2;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $postalCode;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $city;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $country;

    /**
     * @Gedmo\Timestampable(on="change", field={"weight"})
     * @ORM\Column(name="weight_changed", type="datetime", nullable=true)
     * @Expose
    */
    protected $weightChangedAt;

    /**
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\Document")
    */
    protected $avatar;

    /**
    * @Expose
    * @ORM\Column(type="integer", nullable=true)
    * @Groups({"simple"})
    */
    protected $interventionDay;

    /**
    * @Expose
    * @ORM\Column(type="integer", nullable=true)
    * @Groups({"simple"})
    */
    protected $interventionMonth;

    /**
    * @Expose
    * @ORM\Column(type="integer", nullable=true)
    * @Groups({"simple"})
    */
    protected $interventionYear;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    * @Groups({"simple"})
    */
    protected $interventionInfo;

    /**
    * @Expose
    * @ORM\Column(type="text", nullable=true)
    * @Groups({"simple"})
    */
    protected $otherInfo;

    public function __construct()
    {
        parent::__construct();
        $this->folder = uniqid('umdt-');
        $this->organization = null;
        $this->smoker = false;
        $this->phoneNumber = '';
        $this->postalCode = '';
        $this->country = 'France';
        $this->city = '';
        $this->address = '';
        $this->addressMore = '';
        $this->addressMore2 = '';
        $this->avatar = null;
        $this->interventionInfo = '';
        $this->otherInfo = '';
        $this->expertise = 'Médecin';
        $this->invites = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->listings = new ArrayCollection();
        $this->itemActivities = new ArrayCollection();
        $this->userListings = new ArrayCollection();
        $this->doctorPatients = new ArrayCollection();
        $this->patientDoctors = new ArrayCollection();
    }

    /**
    * @VirtualProperty
    * @Groups({"simple"})
    */
    public function customUserListings()
    {
        $ret = array();
        foreach ($this->userListings as $userListing) {
            $ret[] = array(
                'created_at' => $userListing->getCreatedAt(),
                'patient_id' => $userListing->getPatient()->getId(),
                'listing_slug' => $userListing->getListing()->getSlug(),
            );
        }
        return $ret;
    }


    /**
    * @VirtualProperty
    * @Groups({"simple"})
    */
    public function avatarPath()
    {
        if ($this->avatar !== null) {
            return $this->avatar->getFullPath();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getSentMessagesSlugs()
    {
        return $this->sentMessages->map(function($message) {
            return $message->getSlug();
        });

        return array();
    }

    /**
    * @VirtualProperty
    */
    public function getReceivedMessagesSlugs()
    {
        return $this->receivedMessages->map(function($message) {
            return $message->getSlug();
        });

        return array();
    }

    /**
    * @VirtualProperty
    */
    public function getInvitesSlugs()
    {
        return $this->invites->map(function($invite) {
            return $invite->getSlug();
        });

        return null;
    }

    /**
    * @VirtualProperty
    * @Groups({"simple"})
    */
    public function getOrganizationSlug()
    {
        if ($this->organization !== null) {
            return $this->organization->getSlug();
        }

        return null;
    }

    /**
    * @VirtualProperty
    * @Groups({"simple"})
    */
    public function lastLogin()
    {
        return $this->getLastLogin();
    }

    /**
    * @VirtualProperty
    * @Groups({"simple"})
    */
    public function getPrintableName()
    {
        if (!empty($this->firstname) && !empty($this->lastname)) {
            return ucfirst($this->firstname) . ' ' . ucfirst($this->lastname);
        } else {
            return $this->email;
        }
    }

    /**
    * @VirtualProperty
    * @Groups({"simple"})
    */
    public function organizationName()
    {
        if ($this->organization !== null) {
            return $this->organization->getName();
        }
        return '';
    }

    /**
     * Getter for expertise
     * @return mixed
     */
    public function getExpertise()
    {
        return $this->expertise;
    }

    /**
     * Setter for expertise
     * @param mixed $expertise Value to set
     * @return self
     */
    public function setExpertise($expertise)
    {
        $this->expertise = $expertise;
        return $this;
    }

    /**
     * Getter for interventionInfo
     * @return mixed
     */
    public function getInterventionInfo()
    {
        return $this->interventionInfo;
    }

    /**
     * Setter for interventionInfo
     * @param mixed $interventionInfo Value to set
     * @return self
     */
    public function setInterventionInfo($interventionInfo)
    {
        $this->interventionInfo = $interventionInfo;
        return $this;
    }

    /**
     * Getter for interventionYear
     * @return mixed
     */
    public function getInterventionYear()
    {
        return $this->interventionYear;
    }

    /**
     * Setter for interventionYear
     * @param mixed $interventionYear Value to set
     * @return self
     */
    public function setInterventionYear($interventionYear)
    {
        $this->interventionYear = $interventionYear;
        return $this;
    }

    /**
     * Getter for interventionMonth
     * @return mixed
     */
    public function getInterventionMonth()
    {
        return $this->interventionMonth;
    }

    /**
     * Setter for interventionMonth
     * @param mixed $interventionMonth Value to set
     * @return self
     */
    public function setInterventionMonth($interventionMonth)
    {
        $this->interventionMonth = $interventionMonth;
        return $this;
    }

    /**
     * Getter for interventionDay
     * @return mixed
     */
    public function getInterventionDay()
    {
        return $this->interventionDay;
    }

    /**
     * Setter for interventionDay
     * @param mixed $interventionDay Value to set
     * @return self
     */
    public function setInterventionDay($interventionDay)
    {
        $this->interventionDay = $interventionDay;
        return $this;
    }

    /**
     * Getter for otherInfo
     * @return mixed
     */
    public function getOtherInfo()
    {
        return $this->otherInfo;
    }

    /**
     * Setter for otherInfo
     * @param mixed $otherInfo Value to set
     * @return self
     */
    public function setOtherInfo($otherInfo)
    {
        $this->otherInfo = $otherInfo;
        return $this;
    }

    /**
     * Getter for avatar
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Setter for avatar
     * @param mixed $avatar Value to set
     * @return self
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getItems()
    {
        $ret = [];
        foreach ($this->listings as $l) {
            foreach ($l->getItems() as $i) {
                $ret[] = $i;
            }
        }
        return $ret;
    }

    /**
     * Getter for itemActivities
     * @return mixed
     */
    public function getItemActivities()
    {
        return $this->itemActivities;
    }

    /**
     * Setter for itemActivities
     * @param mixed $itemActivities Value to set
     * @return self
     */
    public function setItemActivities($itemActivities)
    {
        $this->itemActivities = $itemActivities;
        return $this;
    }

    /**
     * Add for itemActivities
     * @return mixed
     */
     public function addItemActivity($itemActivitie)
     {
         $this->itemActivities[] = $itemActivitie;
         return $this;
     }

    /**
     * Remove for itemActivities
     * @param mixed $itemActivitie Value to set
     * @return self
     */
    public function removeItemActivity($itemActivitie)
    {
        $this->itemActivities->removeElement($itemActivitie);
        return $this;
    }

    /**
     * Getter for listings
     * @return mixed
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * Setter for listings
     * @param mixed $listings Value to set
     * @return self
     */
    public function setListings($listings)
    {
        $this->listings = $listings;
        return $this;
    }

    /**
     * Add for listings
     * @return mixed
     */
     public function addListing($listing)
     {
         $this->listings[] = $listing;
         return $this;
     }

    /**
     * Remove for listings
     * @param mixed $listing Value to set
     * @return self
     */
    public function removeListing($listing)
    {
        $this->listings->removeElement($listing);
        return $this;
    }

    /**
     * Getter for categories
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Setter for categories
     * @param mixed $categories Value to set
     * @return self
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * Add for categories
     * @return mixed
     */
     public function addCategory($category)
     {
         $this->categories[] = $category;
         $category->setUser($this);
         return $this;
     }

    /**
     * Remove for categories
     * @param mixed $categorie Value to set
     * @return self
     */
    public function removeCategory($categorie)
    {
        $this->categories->removeElement($categorie);
        return $this;
    }

    /**
     * Getter for documents
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Setter for documents
     * @param mixed $documents Value to set
     * @return self
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
        return $this;
    }

    /**
     * Add for documents
     * @return mixed
     */
     public function addDocument($document)
     {
         $this->documents[] = $document;
         $document->setUser($this);
         return $this;
     }

    /**
     * Remove for documents
     * @param mixed $document Value to set
     * @return self
     */
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);
        return $this;
    }

    /**
     * Getter for invites
     * @return mixed
     */
    public function getInvites()
    {
        return $this->invites;
    }

    /**
     * Setter for invites
     * @param mixed $invites Value to set
     * @return self
     */
    public function setInvites($invites)
    {
        $this->invites = $invites;
        return $this;
    }

    /**
     * Add for invites
     * @return mixed
     */
     public function addInvite($invite)
     {
         $this->invites[] = $invite;
         return $this;
     }

    /**
     * Remove for invites
     * @param mixed $invite Value to set
     * @return self
     */
    public function removeInvite($invite)
    {
        $this->invites->removeElement($invite);
        return $this;
    }

    /**
     * Getter for folder
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
    }

    /**
     * Getter for organization
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Setter for organization
     * @param mixed $organization Value to set
     * @return self
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        $organization->addUser($this);
        return $this;
    }

    /**
     * Getter for maidenname
     * @return mixed
     */
    public function getMaidenname()
    {
        return $this->maidenname;
    }

    /**
     * Setter for maidenname
     * @param mixed $maidenname Value to set
     * @return self
     */
    public function setMaidenname($maidenname)
    {
        $this->maidenname = $maidenname;
        return $this;
    }

    /**
     * Getter for type
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Setter for type
     * @param mixed $type Value to set
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Getter for birthdayYear
     * @return mixed
     */
    public function getBirthdayYear()
    {
        return $this->birthdayYear;
    }

    /**
     * Setter for birthdayYear
     * @param mixed $birthdayYear Value to set
     * @return self
     */
    public function setBirthdayYear($birthdayYear)
    {
        $this->birthdayYear = $birthdayYear;
        return $this;
    }

    /**
     * Getter for birthdayMonth
     * @return mixed
     */
    public function getBirthdayMonth()
    {
        return $this->birthdayMonth;
    }

    /**
     * Setter for birthdayMonth
     * @param mixed $birthdayMonth Value to set
     * @return self
     */
    public function setBirthdayMonth($birthdayMonth)
    {
        $this->birthdayMonth = $birthdayMonth;
        return $this;
    }

    /**
     * Getter for birthdayDay
     * @return mixed
     */
    public function getBirthdayDay()
    {
        return $this->birthdayDay;
    }

    /**
     * Setter for birthdayDay
     * @param mixed $birthdayDay Value to set
     * @return self
     */
    public function setBirthdayDay($birthdayDay)
    {
        $this->birthdayDay = $birthdayDay;
        return $this;
    }

    /**
     * Getter for smoker
     * @return mixed
     */
    public function getSmoker()
    {
        return $this->smoker;
    }

    /**
     * Setter for smoker
     * @param mixed $smoker Value to set
     * @return self
     */
    public function setSmoker($smoker)
    {
        $this->smoker = $smoker;
        return $this;
    }

    /**
     * Getter for phoneNumber
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for phoneNumber
     * @param mixed $phoneNumber Value to set
     * @return self
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * Getter for weight
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Setter for weight
     * @param mixed $weight Value to set
     * @return self
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * Getter for height
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Setter for height
     * @param mixed $height Value to set
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Getter for gender
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Setter for gender
     * @param mixed $gender Value to set
     * @return self
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Getter for ipp
     * @return mixed
     */
    public function getIpp()
    {
        return $this->ipp;
    }

    /**
     * Setter for ipp
     * @param mixed $ipp Value to set
     * @return self
     */
    public function setIpp($ipp)
    {
        $this->ipp = $ipp;
        return $this;
    }

    /**
     * Getter for firstname
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Setter for firstname
     * @param mixed $firstname Value to set
     * @return self
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return ;
    }

    /**
     * Getter for lastname
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Setter for lastname
     * @param mixed $lastname Value to set
     * @return self
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return ;
    }

    /**
     * Getter for sentMessages
     * @return mixed
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    /**
     * Setter for sentMessages
     * @param mixed $sentMessages Value to set
     * @return self
     */
    public function setSentMessages($sentMessages)
    {
        $this->sentMessages = $sentMessages;
        return $this;
    }

    /**
     * Add for sentMessages
     * @return mixed
     */
     public function addSentMessage($sentMessage)
     {
         $this->sentMessages[] = $sentMessage;
         return $this;
     }

    /**
     * Remove for sentMessages
     * @param mixed $sentMessage Value to set
     * @return self
     */
    public function removeSentMessage($sentMessage)
    {
        $this->sentMessages->removeElement($sentMessage);
        return $this;
    }

    /**
     * Getter for receivedMessages
     * @return mixed
     */
    public function getReceivedMessages()
    {
        return $this->receivedMessages;
    }

    /**
     * Setter for receivedMessages
     * @param mixed $receivedMessages Value to set
     * @return self
     */
    public function setReceivedMessages($receivedMessages)
    {
        $this->receivedMessages = $receivedMessages;
        return $this;
    }

    /**
     * Add for receivedMessages
     * @return mixed
     */
     public function addReceivedMessage($receivedMessage)
     {
         $this->receivedMessages[] = $receivedMessage;
         return $this;
     }

    /**
     * Remove for receivedMessages
     * @param mixed $receivedMessage Value to set
     * @return self
     */
    public function removeReceivedMessage($receivedMessage)
    {
        $this->receivedMessages->removeElement($receivedMessage);
        return $this;
    }

    /**
     * Getter for postalCode
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Setter for postalCode
     * @param mixed $postalCode Value to set
     * @return self
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * Getter for city
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for city
     * @param mixed $city Value to set
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Getter for country
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for country
     * @param mixed $country Value to set
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Getter for addressMore
     * @return mixed
     */
    public function getAddressMore()
    {
        return $this->addressMore;
    }

    /**
     * Setter for addressMore
     * @param mixed $addressMore Value to set
     * @return self
     */
    public function setAddressMore($addressMore)
    {
        $this->addressMore = $addressMore;
        return $this;
    }

    /**
     * Getter for addressMore2
     * @return mixed
     */
    public function getAddressMore2()
    {
        return $this->addressMore2;
    }

    /**
     * Setter for addressMore2
     * @param mixed $addressMore2 Value to set
     * @return self
     */
    public function setAddressMore2($addressMore2)
    {
        $this->addressMore2 = $addressMore2;
        return $this;
    }

    /**
     * Getter for address
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Setter for address
     * @param mixed $address Value to set
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Getter for weightChangedAt
     * @return mixed
     */
    public function getWeightChangedAt()
    {
        return $this->weightChangedAt;
    }

    /**
     * Getter for userListings
     * @return mixed
     */
    public function getUserListings()
    {
        return $this->userListings;
    }

    /**
     * Setter for userListings
     * @param mixed $userListings Value to set
     * @return self
     */
    public function setUserListings($userListings)
    {
        $this->userListings = $userListings;
        return $this;
    }

    /**
     * Add for userListings
     * @return mixed
     */
     public function addUserListing($userListing)
     {
         $this->userListings[] = $userListing;
         return $this;
     }

    /**
     * Remove for userListings
     * @param mixed $userListing Value to set
     * @return self
     */
    public function removeUserListing($userListing)
    {
        $this->userListings->removeElement($userListing);
        return $this;
    }

    /**
     * Getter for patientDoctors
     * @return mixed
     */
    public function getPatientDoctors()
    {
        return $this->patientDoctors;
    }

    /**
     * Setter for patientDoctors
     * @param mixed $patientDoctors Value to set
     * @return self
     */
    public function setPatientDoctors($patientDoctors)
    {
        $this->patientDoctors = $patientDoctors;
        return $this;
    }

    /**
     * Add for patientDoctors
     * @return mixed
     */
     public function addPatientDoctor($patientDoctor)
     {
         $this->patientDoctors[] = $patientDoctor;
         return $this;
     }

    /**
     * Remove for patientDoctors
     * @param mixed $patientDoctor Value to set
     * @return self
     */
    public function removePatientDoctor($patientDoctor)
    {
        $this->patientDoctors->removeElement($patientDoctor);
        return $this;
    }

    /**
     * Getter for doctorPatients
     * @return mixed
     */
    public function getDoctorPatients()
    {
        return $this->doctorPatients;
    }

    /**
     * Setter for doctorPatients
     * @param mixed $doctorPatients Value to set
     * @return self
     */
    public function setDoctorPatients($doctorPatients)
    {
        $this->doctorPatients = $doctorPatients;
        return $this;
    }

    /**
     * Add for doctorPatients
     * @return mixed
     */
     public function addDoctorPatient($doctorPatient)
     {
         $this->doctorPatients[] = $doctorPatient;
         return $this;
     }

    /**
     * Remove for doctorPatients
     * @param mixed $doctorPatient Value to set
     * @return self
     */
    public function removeDoctorPatient($doctorPatient)
    {
        $this->doctorPatients->removeElement($doctorPatient);
        return $this;
    }

}
