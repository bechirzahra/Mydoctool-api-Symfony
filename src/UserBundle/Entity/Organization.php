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
 * @ORM\Entity(repositoryClass="UserBundle\Repository\OrganizationRepository")
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class Organization
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
    protected $name;

    /**
    * @ORM\Column(type="string")
    * @Expose
    */
    protected $slug;

    /**
    * @ORM\Column(type="string", nullable=true)
    * @Expose
    */
    protected $groupName;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    */
    protected $groupId;

    /**
    * @Expose
    * @ORM\Column(type="string", nullable=true)
    */
    protected $url;

    /**
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\Document")
    */
    protected $logo;

    /**
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\Document")
    */
    protected $image;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Expose
    */
    protected $createdAt;

    /**
    * @ORM\OneToMany(targetEntity="UserBundle\Entity\User", mappedBy="organization")
    */
    protected $users;

    /**
    * @ORM\OneToMany(targetEntity="UserBundle\Entity\Invite", mappedBy="fromOrganization")
    */
    protected $invites;

    function __construct() {
        $this->slug = uniqid('org-');
        $this->name = '';
        $this->groupName = '';
        $this->groupId = '';
        $this->url = '';
        $this->logo = null;
        $this->image = null;
        $this->users = new ArrayCollection();
        $this->invites = new ArrayCollection();
    }

    /**
    *   @VirtualProperty
    */
    public function getLogoPath()
    {
        if ($this->logo !== null) {
            return $this->logo->getFullPath();
        }
        return null;
    }

    /**
    *   @VirtualProperty
    */
    public function getImagePath()
    {
        if ($this->image !== null) {
            return $this->image->getFullPath();
        }
        return null;
    }

    /**
    *   @VirtualProperty
    */
    public function getUsersIds()
    {
        return $this->users->map(function($user) {
            return $user->getId();
        });
    }

    /**
    *   @VirtualProperty
    */
    public function getInvitesSlugs()
    {
        return $this->invites->map(function($invite) {
            return $invite->getSlug();
        });
    }

    // public function getAllPatientsIds()
    // {
    //     $ret = [];

    //     foreach ($this->users->toArray() as $user) {
    //         $ret = array_merge($ret, $user->getLinkedUsersIds()->toArray());
    //     }

    //     return $ret;
    // }

    // public function getAllPatients()
    // {
    //     $ret = [];

    //     foreach ($this->users->toArray() as $user) {
    //         $ret = array_merge($ret, $user->getLinkedUsers()->toArray());
    //     }

    //     return $ret;
    // }

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
     * Setter for id
     * @param mixed $id Value to set
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Getter for name
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for name
     * @param mixed $name Value to set
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Getter for groupName
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Setter for groupName
     * @param mixed $groupName Value to set
     * @return self
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
        return $this;
    }

    /**
     * Getter for groupId
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Setter for groupId
     * @param mixed $groupId Value to set
     * @return self
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * Getter for logo
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Setter for logo
     * @param mixed $logo Value to set
     * @return self
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * Getter for url
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for url
     * @param mixed $url Value to set
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Getter for users
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Setter for users
     * @param mixed $users Value to set
     * @return self
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * Add for users
     * @return mixed
     */
     public function addUser($user)
     {
         $this->users[] = $user;
         return $this;
     }

    /**
     * Remove for users
     * @param mixed $user Value to set
     * @return self
     */
    public function removeUser($user)
    {
        $this->users->removeElement($user);
        return $this;
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
     * Getter for image
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Setter for image
     * @param mixed $image Value to set
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

}