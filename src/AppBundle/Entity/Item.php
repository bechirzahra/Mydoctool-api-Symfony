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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ItemRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="integer")
 * @ORM\DiscriminatorMap({1 = "Question", 2 = "Notice", 3 = "Task"})
 * @ExclusionPolicy("all")
 */
abstract class Item
{
    const TYPE_QUESTION = 1;
    const TYPE_NOTICE = 2;
    const TYPE_TASK = 3;

    const SIGN_EQUAL = 20;
    const SIGN_DIFF = 21;
    const SIGN_SUP = 22;
    const SIGN_INF = 23;
    // const SIGN_ANSWERED = 24;
    // const SIGN_NOT_ANSWERED = 25;

    const DURATION_TYPE_END = 0;
    const DURATION_TYPE_DATE = 1;

    const FREQUENCY_ONCE = 0;
    const FREQUENCY_EVERY_DAY = 1;
    const FREQUENCY_EVERY_TWO_DAYS = 2;
    const FREQUENCY_EVERY_THREE_DAYS = 3;
    const FREQUENCY_EVERY_WEEK = 4;
    const FREQUENCY_EVERY_MONTH = 5;
    const FREQUENCY_TWICE_A_MONTH = 6;

    const ANSWER_VALUE = 0;
    const ANSWER_DATE = 1;

    const LOGIC_AND = 0;
    const LOGIC_OR = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
    * @ORM\Column(type="string")
    * @Expose
    **/
    protected $name;

    /**
    * @ORM\Column(type="text")
    * @Expose
    **/
    protected $text;

    /**
    * @ORM\Column(type="json_array")
    * @Expose
    **/
    protected $frequencies;

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
    * @ORM\Column(type="smallint", name="order_c")
    * @Expose
    */
    protected $orderC;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Listing", inversedBy="items")
    */
    protected $listing;

    /**
    * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Document")
    * @ORM\JoinTable(name="items_documents",
    *      joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")},
    *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", unique=true)}
    *      )
    * @Expose
    */
    protected $documents;

    /**
    * @ORM\Column(type="json_array")
    * @Expose
    **/
    protected $alerts;

    /**
    * @ORM\Column(type="json_array", name="options", nullable=true)
    * @Expose
    **/
    protected $options;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\ItemActivity", mappedBy="item", cascade={"remove"})
    */
    protected $itemActivities;

    public function __construct($name = 'New Item', $text = '', $order = 0)
    {
        $this->name = $name;
        $this->text = $text;
        $this->orderC = $order;
        $this->slug = uniqid('ite-');
        $this->listing = null;
        $this->alerts = array();
        $this->frequencies = array();
        $this->documents = new ArrayCollection();
        $this->itemActivities = new ArrayCollection();
    }

    /**
    * This Property fixes the JMS serializer bug by encoding itselft a json_array
    * @VirtualProperty
    */
    public function tFrequencies()
    {
        return json_encode($this->frequencies);
    }

    /**
    * This Property fixes the JMS serializer bug by encoding itselft a json_array
    * @VirtualProperty
    */
    public function tAlerts()
    {
        return json_encode($this->alerts);
    }

    /**
    * This Property fixes the JMS serializer bug by encoding itselft a json_array
    * @VirtualProperty
    */
    public function tOptions()
    {
        return json_encode($this->options);
    }

    /**
    * @VirtualProperty
    */
    public function listingSlug()
    {
        return $this->listing->getSlug();
    }

    /**
    * @VirtualProperty
    */
    public function getOwnerId()
    {
        return $this->listing->getOwner()->getId();
    }

    public function getUser()
    {
        return $this->listing->getOwner();
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
     * Getter for options
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Setter for options
     * @param mixed $options Value to set
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Getter for frequencies
     * @return mixed
     */
    public function getFrequencies()
    {
        return $this->frequencies;
    }

    /**
     * Setter for frequencies
     * @param mixed $frequencies Value to set
     * @return self
     */
    public function setFrequencies($frequencies)
    {
        $this->frequencies = $frequencies;
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

    public function getTypeAsString()
    {
        switch ($this->itype) {
            case self::TYPE_QUESTION:
                return 'question';
                break;

            case self::TYPE_TASK:
                return 'task';
                break;

            case self::TYPE_NOTICE:
                return 'notice';
                break;
        }
    }

    public function setTypeFromString($type)
    {
        switch ($type) {
            case 'question':
                $this->itype = self::TYPE_QUESTION;
                break;

            case 'task':
                $this->itype = self::TYPE_TASK;
                break;

            case 'notice':
                $this->itype = self::TYPE_NOTICE;
                break;
        }
    }

    /**
     * Getter for alerts
     * @return mixed
     */
    public function getAlerts()
    {
        return $this->alerts;
    }

    /**
     * Setter for alerts
     * @param mixed $alerts Value to set
     * @return self
     */
    public function setAlerts($alerts)
    {
        $this->alerts = $alerts;
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
     * Getter for order
     * @return mixed
     */
    public function getOrderC()
    {
        return $this->orderC;
    }

    /**
     * Setter for order
     * @param mixed $order Value to set
     * @return self
     */
    public function setOrderC($order)
    {
        $this->orderC = $order;
        return ;
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
     * Setter for listing
     * @param mixed $listing Value to set
     * @return self
     */
    public function setListing($listing)
    {
        $this->listing = $listing;
        return ;
    }

}