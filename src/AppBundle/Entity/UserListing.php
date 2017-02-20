<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class UserListing
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     */
    protected $patient;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Listing")
     */
    protected $listing;

    /**
     * @ORM\Column(name="created", type="datetime")
     * @Expose
    */
    protected $createdAt;

    public function __construct($patient, $listing)
    {
        $this->patient = $patient;
        $patient->addUserListing($this);
        $this->listing = $listing;
        $listing->addUserListing($this);
        $this->createdAt = new \DateTime();
    }

    /** @VirtualProperty **/
    public function getPatientId()
    {
        return $this->patient->getId();
    }

    /** @VirtualProperty **/
    public function getListingSlug()
    {
        return $this->listing->getSlug();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Getter for patient
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Getter for listing
     * @return mixed
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * Getter for createdAt
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Setter for createdAt
     * @param mixed $createdAt Value to set
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}