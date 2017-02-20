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
class Message
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
    * @ORM\Column(type="string", nullable=true)
    * @Expose
    **/
    protected $name;

    /**
    * @ORM\Column(type="string")
    * @Expose
    */
    protected $slug;

    /**
    * @ORM\Column(type="text")
    * @Expose
    */
    protected $text;

    /**
    * @ORM\Column(name="is_read", type="boolean")
    * @Expose
    */
    protected $read;

    /**
    * @ORM\Column(type="datetime", nullable=true)
    * @Expose
    */
    protected $readAt;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     * @Expose
    */
    protected $createdAt;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="sentMessages")
    */
    protected $fromUser;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="receivedMessages")
    */
    protected $toUser;

    /**
    * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Document")
    * @ORM\JoinTable(name="message_documents",
    *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
    *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", unique=true)}
    *      )
    * @Expose
    */
    protected $documents;

    public function __construct()
    {
        $this->text = '';
        $this->name = '';
        $this->filename = '';
        $this->slug = uniqid('mes-');
        $this->read = false;
        $this->readAt = null;
        $this->fromUser = null;
        $this->toUser = null;
        $this->documents = new ArrayCollection();
    }

    /**
    * @VirtualProperty
    */
    public function getFromUserPrintable()
    {
        if ($this->fromUser !== null) {
            return $this->fromUser->getPrintableName();
        }
        return '';
    }

    /**
    * @VirtualProperty
    */
    public function getFromUserId()
    {
        if ($this->fromUser !== null) {
            return $this->fromUser->getId();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getToUserId()
    {
        if ($this->toUser !== null) {
            return $this->toUser->getId();
        }
        return null;
    }

    /**
    * @VirtualProperty
    */
    public function getDocumentsSlugs()
    {
        return $this->documents->map(function($document){
            return $document->getSlug();
        });
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
     * Getter for slug
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }


    /**
     * Getter for filename
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename()
    {
        $ext = '';
        if ($this->file !== null) {
            $ext = '.' . $this->file->guessExtension();
        }
        $this->filename = uniqid() . $ext;
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
        return ;
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
     * Getter for read
     * @return mixed
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Setter for read
     * @param mixed $read Value to set
     * @return self
     */
    public function setRead($read)
    {
        $this->read = $read;
        $this->readAt = new \Datetime;
        return $this;
    }

    public function isRead()
    {
        return $this->read;
    }

    public function getReadAt()
    {
        return $this->readAt;
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
     * Getter for toUser
     * @return mixed
     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * Setter for toUser
     * @param mixed $toUser Value to set
     * @return self
     */
    public function setToUser($toUser)
    {
        $this->toUser = $toUser;
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
     * Getter for createdAt
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}