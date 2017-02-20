<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity(repositoryClass="UserBundle\Repository\InviteRepository")
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class Invite
{
    const REGISTER_JOIN_ORGANIZATION_MANAGER = 0;
    const JOIN_ORGANIZATION_MANAGER = 1;
    const REGISTER_JOIN_ORGANIZATION_DOCTOR = 2;
    const JOIN_ORGANIZATION_DOCTOR = 3;
    const REGISTER_USER = 4;
    const REGISTER_ANSWER = 5;
    const ANSWER = 6;

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
    * @ORM\Column(type="smallint")
    * @Expose
    */
    protected $type;

    /**
    * @ORM\Column(name="accepted", type="boolean")
    * @Expose
    */
    protected $accepted;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Expose
    */
    protected $createdAt;

    /**
    * @ORM\Column(type="string")
    * @Expose
    */
    protected $toEmail;

    /**
     * @ORM\OneToOne(targetEntity="UserBundle\Entity\User", inversedBy="invite")
     * @Expose
     */
    protected $user;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\Organization", inversedBy="invites")
    */
    protected $fromOrganization;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="invites")
    */
    protected $fromUser;

    /**
    * @ORM\Column(type="json_array")
    * @Expose
    */
    protected $moreData;

    // the Listing
    protected $resource;

    function __construct($toEmail) {
        $this->slug = uniqid('inv-');
        $this->toEmail = $toEmail;
        $this->accepted = false;
        $this->organization = null;
        $this->moreData = array();
    }

    /**
    * @VirtualProperty
    */
    public function getFromOrganizationSlug()
    {
        if ($this->fromOrganization !== null) {
            return $this->fromOrganization->getSlug();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getPrintableFromOrganization()
    {
        if ($this->fromOrganization !== null) {
            return $this->fromOrganization->getName();
        }
        return '';
    }

    /**
    * @VirtualProperty
    */
    public function getFromUserSlug()
    {
        if ($this->fromUser !== null) {
            return $this->fromUser->getId();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getPrintableFromUser()
    {
        if ($this->fromUser !== null) {
            return $this->fromUser->getPrintableName();
        }
        return '';
    }

    /**
    * @VirtualProperty
    */
    public function getPrintableType()
    {
        switch($this->type) {
            case Invite::REGISTER_JOIN_ORGANIZATION_MANAGER:
                return 'Manager register';
                break;
            case Invite::JOIN_ORGANIZATION_MANAGER:
                return 'Manager join';
                break;
            case Invite::REGISTER_JOIN_ORGANIZATION_DOCTOR:
                return 'Doctor register';
                break;
            case Invite::JOIN_ORGANIZATION_DOCTOR:
                return 'Doctor join';
                break;
            default:
                return 'Patient';
                break;
        }
    }

    /**
    * @VirtualProperty
    */
    public function getLogoImage()
    {
        if ($this->fromOrganization !== null) {
            $logo = $this->fromOrganization->getLogo();
            if ($logo !== null) {
                return $logo->getFullPath();
            }
        } else {
            $u = $this->fromUser;
            $o = $u->getOrganization();
            if ($o !== null) {
                $logo = $o->getLogo();
                if ($logo !== null) {
                    return $logo->getFullPath();
                }
            }
        }
        return '';
    }

    public function isDoctorType()
    {
        if ($this->type === Invite::REGISTER_JOIN_ORGANIZATION_MANAGER
            || $this->type === Invite::JOIN_ORGANIZATION_MANAGER
            || $this->type === Invite::REGISTER_JOIN_ORGANIZATION_DOCTOR
            || $this->type === Invite::JOIN_ORGANIZATION_DOCTOR
            ) {
            return true;
        }
        return false;
    }

    /**
     * Getter for moreData
     * @return mixed
     */
    public function getMoreData()
    {
        return $this->moreData;
    }

    /**
     * Setter for moreData
     * @param mixed $moreData Value to set
     * @return self
     */
    public function setMoreData($moreData)
    {
        $this->moreData = $moreData;
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
     * Getter for id
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * Getter for accepted
     * @return mixed
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Setter for accepted
     * @param mixed $accepted Value to set
     * @return self
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
        return $this;
    }

    public function isAccepted()
    {
        return $this->accepted;
    }

    /**
     * Getter for user
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Setter for user
     * @param mixed $user Value to set
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Getter for fromUser
     * @return mixed
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Setter for fromUser
     * @param mixed $fromUser Value to set
     * @return self
     */
    public function setFromUser($fromUser)
    {
        $this->fromUser = $fromUser;
        return $this;
    }

    /**
     * Getter for fromOrganization
     * @return mixed
     */
    public function getFromOrganization()
    {
        return $this->fromOrganization;
    }

    /**
     * Setter for fromOrganization
     * @param mixed $fromOrganization Value to set
     * @return self
     */
    public function setFromOrganization($fromOrganization)
    {
        $this->fromOrganization = $fromOrganization;
        return $this;
    }


    /**
     * Getter for toEmail
     * @return mixed
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     * Setter for toEmail
     * @param mixed $toEmail Value to set
     * @return self
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;
        return $this;
    }

}
