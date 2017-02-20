<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AlertRepository")
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class Alert
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
    * @ORM\Column(type="string")
    * @Expose
    */
    protected $slug;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     * @Expose
    */
    protected $createdAt;

    /**
    * @ORM\Column(type="boolean")
    * @Expose
    */
    protected $patientMessage;

    /**
    * @ORM\Column(type="boolean")
    * @Expose
    */
    protected $doctorMessage;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ItemActivity", inversedBy="alerts")
    */
    protected $itemActivity;

    /**
    * @ORM\Column(type="boolean")
    * @Expose
    */
    protected $closed;

    /**
    * @ORM\Column(type="string")
    * @Expose
    */
    protected $alertUid;

    public function __construct($itemActivity, $patientMessage = false, $doctorMessage = false)
    {
        $this->slug = uniqid('ale-');
        $this->setItemActivity($itemActivity);
        $this->patientMessage = $patientMessage;
        $this->doctorMessage = $doctorMessage;
        $this->closed = false;
        $this->alertUid = '';
    }

    /**
    * @VirtualProperty
    */
    public function getItemSlug()
    {
        if ($this->itemActivity !== null) {
            return $this->itemActivity->getItem()->getSlug();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getItemActivitySlug()
    {
        if ($this->itemActivity !== null) {
            return $this->itemActivity->getSlug();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getUserId()
    {
        if ($this->itemActivity !== null) {
            return $this->itemActivity->getUser()->getId();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getListingSlug()
    {
        if ($this->itemActivity !== null) {
            return $this->itemActivity->getItem()->getListing()->getSlug();
        }
        return null;
    }

    /**
     * Getter for alertUid
     * @return mixed
     */
    public function getAlertUid()
    {
        return $this->alertUid;
    }

    /**
     * Setter for alertUid
     * @param mixed $alertUid Value to set
     * @return self
     */
    public function setAlertUid($alertUid)
    {
        $this->alertUid = $alertUid;
        return $this;
    }

    /**
     * Getter for itemActivity
     * @return mixed
     */
    public function getItemActivity()
    {
        return $this->itemActivity;
    }

    /**
     * Setter for itemActivity
     * @param mixed $itemActivity Value to set
     * @return self
     */
    public function setItemActivity($itemActivity)
    {
        $this->itemActivity = $itemActivity;
        $itemActivity->addAlert($this);
        return $this;
    }

    /**
     * Getter for id
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Getter for conditions
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Setter for conditions
     * @param mixed $conditions Value to set
     * @return self
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * Getter for slug
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
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
     * Getter for closed
     * @return mixed
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Setter for closed
     * @param mixed $closed Value to set
     * @return self
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
        return $this;
    }

}