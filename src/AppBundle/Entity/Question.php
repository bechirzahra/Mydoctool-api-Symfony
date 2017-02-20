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

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionRepository")
 */
class Question extends Item
{
    const QUESTION_DATA = 11;
    const QUESTION_SELECT = 12;
    const QUESTION_BOOL = 13;
    const QUESTION_LEVEL = 14;
    const QUESTION_TEXT = 15;

    /**
    * @ORM\Column(type="integer", length=2)
    * @Expose
    */
    protected $questionType;

    /**
    * @ORM\Column(type="string", length=5, nullable=true)
    * @Expose
    */
    protected $unit;

    /**
    * @ORM\Column(type="boolean", nullable=true)
    * @Expose
    */
    protected $textAnswerShort;

    /**
    * @ORM\Column(type="string", length=15, nullable=true)
    * @Expose
    */
    protected $icon;

    /**
    * @ORM\Column(type="integer", length=2, nullable=true)
    * @Expose
    */
    protected $min;

    /**
    * @ORM\Column(type="integer", length=2, nullable=true)
    * @Expose
    */
    protected $max;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @Expose
    */
    private $updatedAt;

    public function __construct($name = 'New Item', $text = '', $order = 0, $questionType = self::QUESTION_DATA)
    {
        parent::__construct($name, $text, $order);
        $this->type = self::TYPE_QUESTION;
        $this->questionType = $questionType;
        $this->options = array();
        $this->textAnswerShort = false;
    }

    /**
     * Getter for type
     * @return mixed
     */
    public function getType()
    {
        return self::TYPE_QUESTION;
    }

    /**
    * @VirtualProperty
    */
    public function printableType()
    {
        return $this->getQuestionTypeAsString();
    }

    public function getQuestionTypeAsString()
    {
        switch ($this->questionType) {
            case self::QUESTION_DATA:
                return 'data';
                break;

            case self::QUESTION_BOOL:
                return 'bool';
                break;

            case self::QUESTION_LEVEL:
                return 'level';
                break;

            case self::QUESTION_SELECT:
                return 'select';
                break;

            case self::QUESTION_TEXT:
                return 'text';
                break;
        }
    }

    public function setQuestionTypeFromString($type)
    {
        switch ($type) {
            case 'text':
                $this->questionType = self::QUESTION_TEXT;
                break;

            case 'select':
                $this->questionType = self::QUESTION_SELECT;
                break;

            case 'data':
                $this->questionType = self::QUESTION_DATA;
                break;

            case 'bool':
                $this->questionType = self::QUESTION_BOOL;
                break;

            case 'level':
                $this->questionType = self::QUESTION_LEVEL;
                break;
        }
    }

    /**
     * Getter for textAnswerShort
     * @return mixed
     */
    public function getTextAnswerShort()
    {
        return $this->textAnswerShort;
    }

    /**
     * Setter for textAnswerShort
     * @param mixed $textAnswerShort Value to set
     * @return self
     */
    public function setTextAnswerShort($textAnswerShort)
    {
        $this->textAnswerShort = $textAnswerShort;
        return $this;
    }

    /**
     * Getter for min
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Setter for min
     * @param mixed $min Value to set
     * @return self
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Getter for max
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Setter for max
     * @param mixed $max Value to set
     * @return self
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Getter for unit
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Setter for unit
     * @param mixed $unit Value to set
     * @return self
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * Getter for icon
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Setter for icon
     * @param mixed $icon Value to set
     * @return self
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
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
     * Setter for updatedAt
     * @param mixed $updatedAt Value to set
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Getter for questionType
     * @return mixed
     */
    public function getQuestionType()
    {
        return $this->questionType;
    }

    /**
     * Setter for questionType
     * @param mixed $questionType Value to set
     * @return self
     */
    public function setQuestionType($questionType)
    {
        $this->questionType = $questionType;
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


}