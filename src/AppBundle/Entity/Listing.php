<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ListingRepository")
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class Listing
{

    const UNIT_DAY = 0;
    const UNIT_WEEK = 1;
    const UNIT_MONTH = 2;
    const UNIT_END = 3;
    const UNIT_YEAR = 4;

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
    **/
    protected $name;

    /**
    * @ORM\Column(type="string", nullable=true)
    * @Expose
    **/
    protected $color;

    /**
    * @ORM\Column(type="text", nullable=true)
    * @Expose
    **/
    protected $text;

    /**
    * @ORM\Column(type="boolean")
    * @Expose
    **/
    protected $published;

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
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category", inversedBy="listings", cascade={"persist"})
    */
    protected $category;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Item", mappedBy="listing", cascade={"remove"})
    * @MaxDepth(10)
    */
    protected $items;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="listings")
    */
    protected $owner;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserListing", mappedBy="listing", cascade={"remove"})
    */
    protected $userListings;

    /**
    * @ORM\Column(type="boolean")
    * @Expose
    **/
    protected $isTemplate;

    /**
    * @ORM\Column(type="integer", name="duration_value", nullable=true)
    * @Expose
    **/
    protected $durationValue;

    /**
    * @ORM\Column(type="smallint", name="duration_unit", nullable=true)
    * @Expose
    **/
    protected $durationUnit;

    public function __construct($owner, $category = null)
    {
        $this->name = '';
        $this->color = '';
        $this->text = 'Ma description de protocole';
        $this->published = false;
        $this->slug = uniqid('lis-');
        $this->category = $category;
        $this->setOwner($owner);
        $this->duration = 0;
        $this->isTemplate = false;
        $this->durationValue = 0;
        $this->durationUnit = 0;
        $this->items = new ArrayCollection();
        $this->userListings = new ArrayCollection();
    }

    /**
    * @VirtualProperty
    */
    public function getCategorySlug()
    {
        if ($this->category !== null) {
            return $this->category->getSlug();
        }
        return null;
    }

    /**
     * Getter for durationUnit
     * @return mixed
     */
    public function getDurationUnit()
    {
        return $this->durationUnit;
    }

    /**
     * Setter for durationUnit
     * @param mixed $durationUnit Value to set
     * @return self
     */
    public function setDurationUnit($durationUnit)
    {
        $this->durationUnit = $durationUnit;
        return $this;
    }

    /**
     * Getter for durationValue
     * @return mixed
     */
    public function getDurationValue()
    {
        return $this->durationValue;
    }

    /**
     * Setter for durationValue
     * @param mixed $durationValue Value to set
     * @return self
     */
    public function setDurationValue($durationValue)
    {
        $this->durationValue = $durationValue;
        return $this;
    }

    /**
     * Getter for duration
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Setter for duration
     * @param mixed $duration Value to set
     * @return self
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Getter for owner
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Setter for owner
     * @param mixed $owner Value to set
     * @return self
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        $owner->addListing($this);
        return $this;
    }

    /**
     * Getter for published
     * @return mixed
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Setter for published
     * @param mixed $published Value to set
     * @return self
     */
    public function setPublished($published)
    {
        $this->published = $published;
        return $this;
    }

    public function isPublished()
    {
        return $this->published;
    }

    /**
     * Getter for color
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Setter for color
     * @param mixed $color Value to set
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Getter for text
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Setter for text
     * @param mixed $text Value to set
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;
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
        return ;
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
     * Getter for project
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Getter for category
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Setter for category
     * @param mixed $category Value to set
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return ;
    }

    /**
     * Getter for isTemplate
     * @return mixed
     */
    public function getIsTemplate()
    {
        return $this->isTemplate;
    }

    /**
     * Setter for isTemplate
     * @param mixed $isTemplate Value to set
     * @return self
     */
    public function setIsTemplate($isTemplate)
    {
        $this->isTemplate = $isTemplate;
        return $this;
    }

    /**
     * Getter for items
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Setter for items
     * @param mixed $items Value to set
     * @return self
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Add for items
     * @return mixed
     */
     public function addItem($item)
     {
         $this->items[] = $item;
         $item->setListing($this);
         return $this;
     }

    /**
     * Remove for items
     * @param mixed $item Value to set
     * @return self
     */
    public function removeItem($item)
    {
        $this->items->removeElement($item);
        return $this;
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

}