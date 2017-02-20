<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use AppBundle\Entity\Item;
use AppBundle\Entity\Question;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ItemActivityRepository")
 * @ExclusionPolicy("all")
 */
class ItemActivity
{

    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="itemActivities")
    */
    protected $user;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Item", inversedBy="itemActivities")
    */
    protected $item;

    /**
    * @ORM\Column(type="string")
    * @Expose
    */
    protected $slug;

    /**
    * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Document")
    * @ORM\JoinTable(name="item_activities_documents",
    *      joinColumns={@ORM\JoinColumn(name="item_activity_id", referencedColumnName="id")},
    *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", unique=true)}
    *      )
    * @Expose
    */
    protected $documents;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $answerBool;

    /**
    * @ORM\Column(type="float")
    */
    protected $answerInt;

    /**
    * @ORM\Column(type="text")
    */
    protected $answerText;

    /**
    * @ORM\Column(type="json_array")
    */
    protected $answerSelect;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     * @Expose
    */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     * @Expose
    */
    protected $updatedAt;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Alert", mappedBy="itemActivity", cascade={"remove"})
    */
    protected $alerts;

    public function __construct($user = null, $item = null)
    {
        $this->slug = uniqid('ita-');
        $this->setUser($user);
        $this->setItem($item);
        $this->answerBool = false;
        $this->answerInt = -1;
        $this->answerText = '';
        $this->answerSelect = array();
        $this->documents = new ArrayCollection();
        $this->alerts = new ArrayCollection();
    }

    /**
    * @VirtualProperty
    */
    public function done()
    {
        return $this->createdAt->getTimestamp() !== $this->updatedAt->getTimestamp();
    }

    /**
    * @VirtualProperty
    */
    public function userId()
    {
        if ($this->user !== null) {
            return $this->user->getId();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function itemSlug()
    {
        if ($this->item !== null) {
            return $this->item->getSlug();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getAnswer()
    {
        if ($this->item !== null) {
            switch ($this->item->getType()) {
                case Item::TYPE_TASK:
                    return $this->answerBool;
                    break;

                case Item::TYPE_NOTICE:
                    return $this->answerBool;
                    break;

                case Item::TYPE_QUESTION:

                    switch ($this->item->getQuestionType()) {
                        case Question::QUESTION_BOOL:
                            return $this->answerBool;
                            break;

                        case Question::QUESTION_TEXT:
                            return $this->answerText;
                            break;

                        case Question::QUESTION_SELECT:
                            return $this->answerSelect;
                            break;

                        default:
                            return $this->answerInt;
                            break;
                    }

                    return $this->answerBool;
                    break;
            }
        }

        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getAlertsSlugs()
    {
        $ret = [];
        foreach ($this->alerts as $alert) {
            $ret[] = $alert->getSlug();
        }
        return $ret;
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
     * Add for alerts
     * @return mixed
     */
     public function addAlert($alert)
     {
         $this->alerts[] = $alert;
         return $this;
     }

    /**
     * Remove for alerts
     * @param mixed $alert Value to set
     * @return self
     */
    public function removeAlert($alert)
    {
        $this->alerts->removeElement($alert);
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
     * Getter for slug
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
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
        $user->addItemActivity($this);
        return $this;
    }

    /**
     * Getter for item
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    public function getListing()
    {
        return $this->item->getListing();
    }

    /**
     * Setter for item
     * @param mixed $item Value to set
     * @return self
     */
    public function setItem($item)
    {
        $this->item = $item;
        $item->addItemActivity($this);
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
     * Getter for updatedAt
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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
     * Getter for answerBool
     * @return mixed
     */
    public function getAnswerBool()
    {
        return $this->answerBool;
    }

    /**
     * Setter for answerBool
     * @param mixed $answerBool Value to set
     * @return self
     */
    public function setAnswerBool($answerBool)
    {
        $this->answerBool = $answerBool;
        return $this;
    }

    /**
     * Getter for answerInt
     * @return mixed
     */
    public function getAnswerInt()
    {
        return $this->answerInt;
    }

    /**
     * Setter for answerInt
     * @param mixed $answerInt Value to set
     * @return self
     */
    public function setAnswerInt($answerInt)
    {
        $this->answerInt = $answerInt;
        return $this;
    }

    /**
     * Getter for answerText
     * @return mixed
     */
    public function getAnswerText()
    {
        return $this->answerText;
    }

    /**
     * Setter for answerText
     * @param mixed $answerText Value to set
     * @return self
     */
    public function setAnswerText($answerText)
    {
        $this->answerText = $answerText;
        return $this;
    }

    /**
     * Getter for answerSelect
     * @return mixed
     */
    public function getAnswerSelect()
    {
        return $this->answerSelect;
    }

    /**
     * Setter for answerSelect
     * @param mixed $answerSelect Value to set
     * @return self
     */
    public function setAnswerSelect($answerSelect)
    {
        $this->answerSelect = $answerSelect;
        return $this;
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

    /**
     * Setter for updatedAt
     * @param mixed $updatedAt Value to set
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}