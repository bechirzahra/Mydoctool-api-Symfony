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
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserListingRepository")
 * @ORM\Table()
 * @ExclusionPolicy("all")
 */
class Document
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
    * @ORM\Column(type="text")
    * @Expose
    **/
    protected $filename;

    /**
     * @Assert\File(maxSize="6000000")
     */
    public $file;

    /**
    * @Expose()
    */
    public $uri;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     * @Expose
    */
    protected $createdAt;

    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="documents")
    */
    protected $user;

    public function __construct($name = '', $file = null)
    {
        $this->name = $name;
        $this->text = '';
        $this->filename = '';
        $this->slug = uniqid('doc-');
        $this->user = null;
        $this->file = $file;
    }

    /**
    * @VirtualProperty
    */
    public function getPrintableName()
    {
        if ($this->name !== '' && $this->name !== 'undefined') {
            return $this->name;
        }

        if ($this->answer !== null) {
            $fieldType = $this->answer->getFieldType();
            $labels = $fieldType->getOptions();
            return $labels[0]['fr'];
        }
    }

    /**
    * @VirtualProperty
    */
    public function getFullPath()
    {
        return '/' . $this->getFolderPath() . '/' . $this->filename;
    }

    public function getFolderPath()
    {
        if ($this->user !== null) {
            $userFolder = $this->user->getFolder();

            $uri = $userFolder;
        } else {
            $uri = 'public';
        }

        return $uri;
    }

    /**
     * Getter for file
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Setter for file
     * @param mixed $file Value to set
     * @return self
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getAbsolutePath()
    {
        return __DIR__.'/../../../web/uploads' . $this->getFullPath();
    }

    /**
    * @VirtualProperty
    */
    public function getExtension()
    {
        $ext = explode('.', $this->filename);
        return $ext[count($ext) - 1];
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

    public function setForcedFilename($filename)
    {
        $this->filename = $filename;
        return $this;
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
     * Getter for createdAt
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}