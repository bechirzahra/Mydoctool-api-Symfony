<?php

namespace UserBundle\Entity;

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
class DoctorPatient
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
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     */
    protected $doctor;

    /**
    * @ORM\Column(type="boolean")
    * @Expose
    */
    protected $favorite;

    public function __construct($patient, $doctor)
    {
        $this->patient = $patient;
        $patient->addDoctorPatient($this);
        $this->doctor = $doctor;
        $doctor->addDoctorPatient($this);
        $this->favorite = false;
    }

    /** @VirtualProperty **/
    public function getPatientId()
    {
        return $this->patient->getId();
    }

    /** @VirtualProperty **/
    public function getDoctorId()
    {
        return $this->doctor->getId();
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
    * Getter for doctor
    * @return mixed
    */
   public function getDoctor()
   {
       return $this->doctor;
   }

   /**
    * Getter for favorite
    * @return mixed
    */
   public function getFavorite()
   {
       return $this->favorite;
   }

   /**
    * Setter for favorite
    * @param mixed $favorite Value to set
    * @return self
    */
   public function setFavorite($favorite)
   {
       $this->favorite = $favorite;
       return $this;
   }

}