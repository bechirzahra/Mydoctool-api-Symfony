<?php

namespace AppBundle\Model;

use AppBundle\Entity\Task;
use AppBundle\Entity\Notice;
use AppBundle\Entity\Question;

class ItemManager {

    function __construct() {

    }

    public function createItemFromData($rawItemData)
    {
        $newItem = null;
        $type = $rawItemData->printable_type;

        if ($type === 'task') {
            $newItem = new Task(
                $rawItemData->name,
                $rawItemData->text,
                $rawItemData->order_c
            );

            if (isset($rawItemData->repeated)) {
                $newItem->setRepeated($rawItemData->repeated);
            }
        } else if ($type === 'notice') {
            $newItem = new Notice(
                $rawItemData->name,
                $rawItemData->text,
                $rawItemData->order_c
            );
        } else {
            // In this case, the Type is the Question Type
            $newItem = new Question(
                $rawItemData->name,
                $rawItemData->text,
                $rawItemData->order_c
            );

            $newItem->setQuestionTypeFromString($type);

            $otherProps = ['unit', 'textAnswerShort', 'icon', 'min', 'max', 'options'];
            foreach ($otherProps as $prop) {
                if (isset($rawItemData->$prop) && $rawItemData->$prop !== '') {
                    $func = 'set' . ucfirst($prop);
                    $newItem->$func($rawItemData->$prop);
                }
            }
        }

        $newItem->setFrequencies($rawItemData->frequencies);
        $updatedAlerts = $this->sanitizeAlerts($newItem->getSlug(), $rawItemData->alerts);
        $newItem->setAlerts($updatedAlerts);

        return $newItem;
    }

    public function updateItemFromData($existingItem, $rawItemData)
    {
        $otherProps = ['name', 'text', 'unit', 'textAnswerShort', 'icon', 'min', 'max', 'repeated', 'frequencies', 'options'];
        foreach ($otherProps as $prop) {
            if (isset($rawItemData->$prop) && $rawItemData->$prop !== '') {
                $func = 'set' . ucfirst($prop);
                $existingItem->$func($rawItemData->$prop);
            }
        }
        $existingItem->setOrderC($rawItemData->order_c);

        $updatedAlerts = $this->sanitizeAlerts($existingItem->getSlug(), $rawItemData->alerts);
        $existingItem->setAlerts($updatedAlerts);

        return $existingItem;
    }

    // This function aims to replace the slug -1 when creating an Alert to the current Item's slug
    private function sanitizeAlerts($slug, $alerts)
    {
        foreach ($alerts as $alert) {
            $conditions = $alert->conditions;

            foreach ($conditions as $k => $condition) {
                if (isset($condition->base->slug) && $condition->base->slug == -1) {
                    $condition->base->slug = $slug;
                    $conditions[$k] = $condition;
                }
            }

            $alert->conditions = $conditions;
        }
        return $alerts;
    }
}