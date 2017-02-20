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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class Category
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
    **/
    protected $name;

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
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="categories")
    */
    protected $user;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Listing", mappedBy="category")
    */
    protected $listings;


    public function __construct()
    {
        $this->name = '';
        $this->slug = uniqid('cat-');
        $this->user = null;
        $this->listings = new ArrayCollection();
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
        return ;
    }

    /**
     * Add for listings
     * @return mixed
     */
     public function addListing($listing)
     {
         $this->listings[] = $listing;
         return ;
     }

    /**
     * Remove for listings
     * @param mixed $listing Value to set
     * @return self
     */
    public function removeListing($listing)
    {
        $this->listings->removeElement($listing);
        return ;
    }

}