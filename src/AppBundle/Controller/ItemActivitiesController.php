<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Post;

use JMS\Serializer\SerializationContext;

use AppBundle\Form\Type\ItemActivityFormType;
use AppBundle\Entity\ItemActivity;
use AppBundle\Entity\Document;
use AppBundle\Entity\Message;
use AppBundle\Entity\Item;
use AppBundle\Entity\Alert;

/**
 * @RouteResource("ItemActivity")
 */
class ItemActivitiesController extends FOSRestController
{
    public function optionsAction()
    {}

    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('AppBundle:ItemActivity');

        $itemsactivities = $repository->findAll();
        $data = array('items' => $itemsactivities);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    /**
    * @POST("/itemactivities/{itemActivitySlug}")
    */
    public function answerAction(Request $request, $itemActivitySlug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

        $newFiles = array();

        $itemActivity = $em->getRepository('AppBundle:ItemActivity')->findOneBySlug($itemActivitySlug);
        $oldItemActivities = null;

        if (!$itemActivity) {
            throw new NotFoundHttpException('Item Activity not found');
        }

        $form = $this->getForm($itemActivity);
        $form->bind($request);

        if ($form->isValid()) {

            $answerBool = $request->request->get('answerBool');
            if (isset($answerBool)) {
                if ($answerBool == "true") {
                    $itemActivity->setAnswerBool(true);
                } else {
                    $itemActivity->setAnswerBool(false);
                }
            }

            $answerSelect = $request->request->get('answerSelect');
            if (isset($answerSelect)) {
                $itemActivity->setAnswerSelect(json_decode($answerSelect));
            }

            $files = $request->files->get('files');
            $filenames = $request->request->get('files');

            if (isset($files) && count($files) > 0) {

                foreach ($files as $k => $uploadedFile) {
                    $doc = new Document();
                    $doc->file = $uploadedFile;
                    $doc->setFilename();

                    $doc->setName(htmlspecialchars($filenames[$k]));

                    $itemActivity->addDocument($doc);
                    $itemActivity->getUser()->addDocument($doc);

                    $fs->write(
                        $doc->getFullPath(),
                        file_get_contents($doc->file->getPathname())
                    );

                    $em->persist($doc);
                    $newFiles[] = $doc;
                }
            }

            // We flush a first time here to get the new Updated date of ItemActivity
            $em->flush();

            $item = $itemActivity->getItem();
            $alerts = $item->getAlerts();
            $newMessages = array();


            // We should check if it spawns an Alert or not.
            // This is valid for every question answer except Text
            if ($item->getType() === Item::TYPE_QUESTION && $item->getQuestionTypeAsString() !== 'text') {
                foreach ($alerts as $alert) {
                    $conditions = $alert['conditions'];
                    $resultAlert = null;
                    foreach ($conditions as $k => $condition) {
                        $resultCondition = false;
                        $baseValue = null;
                        $endValue = null;

                        // Cast an array to an object
                        $condition = json_decode(json_encode($condition), FALSE);
                        $itemActivitiesBase = $em->getRepository('AppBundle:ItemActivity')->findCByItemSlug($condition->base->slug, $itemActivity->getUser());

                        // We sort those Item Activities by date (the closest Date first)
                        usort($itemActivitiesBase, function($iA, $iB) {
                            if ($iA->getCreatedAt() === $iB->getCreatedAt()) {
                                return 0;
                            }
                            return $iA->getCreatedAt() < $iB->getCreatedAt() ? 1 : -1;
                        });

                        $findId = function($var) use ($itemActivity) {
                            return $var->getId() == $itemActivity->getId();
                        };

                        $itemActivityIdx = array_search(array_filter($itemActivitiesBase, $findId), $itemActivitiesBase);

                        // We get the first value to compare
                        $baseValue = $this->getAlertDate($condition->base->date, $itemActivitiesBase, $itemActivityIdx);

                        if ($baseValue !== null) {
                            if ($condition->answer->type === Item::ANSWER_VALUE) {
                                $endValue = $condition->answer->value;
                            } else {
                                // We get the end value to compare
                                $endValue = $this->getAlertDate($condition->answer->date, $itemActivitiesBase, $itemActivityIdx);
                            }

                            if ($endValue !== null) {
                                switch ($condition->sign) {
                                    case Item::SIGN_EQUAL:
                                        $resultCondition = $baseValue == $endValue;
                                        break;
                                    case Item::SIGN_DIFF:
                                        $resultCondition = $baseValue != $endValue;
                                        break;
                                    case Item::SIGN_SUP:
                                        $resultCondition = $baseValue > $endValue;
                                        break;
                                    case Item::SIGN_INF:
                                        $resultCondition = $baseValue < $endValue;
                                        break;
                                }
                            }
                        }

                        if ($resultAlert === null) {
                            $resultAlert = $resultCondition;
                        } else {
                            if (isset($condition->logic)) {
                                if ($condition->logic === Item::LOGIC_AND) {
                                    $resultAlert = $resultAlert && $resultCondition;
                                } else {
                                    $resultAlert = $resultAlert || $resultCondition;
                                }
                            } else {
                                $resultAlert = $resultCondition;
                            }
                        }
                    }

                    // We check if we should create a new Alert
                    if ($resultAlert !== null && $resultAlert) {
                        $newAlert = new Alert($itemActivity, $alert['patientMessage']['send'], $alert['doctorMessage']['send']);
                        $newAlert->setAlertUid($alert['uid']);

                        $patient = $itemActivity->getUser();
                        $doctor = $item->getUser();

                        if ($alert['patientMessage']['send']) {
                            $patientMessage = new Message();
                            $patientMessage->setFromUser($doctor);
                            $patientMessage->setToUser($patient);
                            $patientMessage->setText($alert['patientMessage']['message']);
                            $em->persist($patientMessage);

                            $newMessages[] = $patientMessage;
                        }

                        if ($alert['doctorMessage']['send']) {
                            $doctorMessage = new Message();
                            $doctorMessage->setFromUser($patient);
                            $doctorMessage->setToUser($doctor);
                            $doctorMessage->setText($alert['doctorMessage']['message']);
                            $em->persist($doctorMessage);
                        }

                        $em->persist($newAlert);
                    }
                }
                // Now check alerts in the future !
                // The user can potentially answer questions "in the wrong order".
                // We should still send the alerts
                foreach ($alerts as $alert) {
                    $conditions = $alert['conditions'];
                    $resultAlert = null;
                    foreach ($conditions as $k => $condition) {
                        $resultCondition = false;
                        $baseValue = null;
                        $endValue = null;

                        // Cast an array to an object
                        $condition = json_decode(json_encode($condition), FALSE);
                        $itemActivitiesBase = $em->getRepository('AppBundle:ItemActivity')->findCByItemSlug($condition->base->slug, $itemActivity->getUser());

                        // We sort those Item Activities by date (the closest Date first)
                        usort($itemActivitiesBase, function($iA, $iB) {
                            if ($iA->getCreatedAt() === $iB->getCreatedAt()) {
                                return 0;
                            }
                            return $iA->getCreatedAt() < $iB->getCreatedAt() ? 1 : -1;
                        });

                        $findId = function($var) use ($itemActivity) {
                            return $var->getId() == $itemActivity->getId();
                        };

                        $itemActivityIdx = array_search(array_filter($itemActivitiesBase, $findId), $itemActivitiesBase);

                        // We get the first value to compare
                        $baseValue = $this->getAlertDate($condition->base->date, $itemActivitiesBase, $itemActivityIdx, function ($x, $y) { return $x - $y; });

                        if ($baseValue !== null) {
                            if ($condition->answer->type === Item::ANSWER_VALUE) {
                                $endValue = $condition->answer->value;
                            } else {
                                // We get the end value to compare
                                $endValue = $this->getAlertDate($condition->answer->date, $itemActivitiesBase, $itemActivityIdx, function ($x, $y) { return $x - $y; });
                            }

                            if ($endValue !== null) {
                                switch ($condition->sign) {
                                    case Item::SIGN_EQUAL:
                                        $resultCondition = $baseValue == $endValue;
                                        break;
                                    case Item::SIGN_DIFF:
                                        $resultCondition = $baseValue != $endValue;
                                        break;
                                    case Item::SIGN_SUP:
                                        $resultCondition = $baseValue > $endValue;
                                        break;
                                    case Item::SIGN_INF:
                                        $resultCondition = $baseValue < $endValue;
                                        break;
                                }
                            }
                        }

                        if ($resultAlert === null) {
                            $resultAlert = $resultCondition;
                        } else {
                            if (isset($condition->logic)) {
                                if ($condition->logic === Item::LOGIC_AND) {
                                    $resultAlert = $resultAlert && $resultCondition;
                                } else {
                                    $resultAlert = $resultAlert || $resultCondition;
                                }
                            } else {
                                $resultAlert = $resultCondition;
                            }
                        }
                    }

                    // We check if we should create a new Alert
                    if ($resultAlert !== null && $resultAlert) {
                        $newAlert = new Alert($itemActivity, $alert['patientMessage']['send'], $alert['doctorMessage']['send']);
                        $newAlert->setAlertUid($alert['uid']);

                        $patient = $itemActivity->getUser();
                        $doctor = $item->getUser();

                        if ($alert['patientMessage']['send']) {
                            $patientMessage = new Message();
                            $patientMessage->setFromUser($doctor);
                            $patientMessage->setToUser($patient);
                            $patientMessage->setText($alert['patientMessage']['message']);
                            $em->persist($patientMessage);

                            $newMessages[] = $patientMessage;
                        }

                        if ($alert['doctorMessage']['send']) {
                            $doctorMessage = new Message();
                            $doctorMessage->setFromUser($patient);
                            $doctorMessage->setToUser($doctor);
                            $doctorMessage->setText($alert['doctorMessage']['message']);
                            $em->persist($doctorMessage);
                        }

                        $em->persist($newAlert);
                    }
                }
            }
            /*
            * If the Item is a Task, we should also mark as "done" every previous
            * itemActivity linked to this Task.
            * We also close any alert link to this previous itemActivities
            */
            else {
                if ($item->getType() === Item::TYPE_TASK) {
                    $user = $itemActivity->getUser();

                    // We should retrieve old item activities
                    $oldItemActivities = $em->getRepository('AppBundle:ItemActivity')->findBy(array(
                        'item' => $item,
                        'user' => $user,
                    ));

                    // Mark them as done
                    foreach ($oldItemActivities as $tempIA) {
                        if (!$tempIA->done()) {
                            $tempIA->setUpdatedAt($itemActivity->getUpdatedAt());

                            // Close current alerts linked to those item activities
                            $currentAlerts = $tempIA->getAlerts();
                            foreach ($currentAlerts as $tempAlert) {
                                $tempAlert->setClosed(true);
                            }
                        }
                    }

                    // We should also close any alert spawned because of the first item Activity
                    if ($itemActivity->getAlerts()->count() > 0) {
                        foreach ($itemActivity->getAlerts() as $tempAlert) {
                            $tempAlert->setClosed(true);
                        }
                    }
                }
            }

            $em->flush();

            $ret = array(
                'itemActivity' => $itemActivity,
                'oldItemActivities' => $oldItemActivities,
                'documents' => $newFiles,
                'messages' => $newMessages
            );

            $view = $this->view($ret);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    public function getAction($itemActivitySlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $itemActivity = $em->getRepository('AppBundle:ItemActivity')->findOneBySlug($itemActivitySlug);
        if (!$itemActivity) {
            throw new NotFoundHttpException('ItemActivity not found.');
        }

        $view = $this->view($itemActivity);

        return $this->handleView($view);
    }

    protected function getForm($itemActivity = null, $method = 'POST')
    {
        return $this->createForm(new ItemActivityFormType($this->getDoctrine()->getEntityManager()), $itemActivity, array('method' => $method));
    }

    public function getAlertDate($date, $itemActivitiesBase, $itemActivityIdx, $add = NULL) {
        if ($add == NULL) {
            $add = function($x, $y) {
                return $x + $y;
            };
        }

        switch ($date) {
            case 'j':
                return $itemActivitiesBase[$itemActivityIdx]->getAnswer();

            case 'j1':
                if (isset($itemActivitiesBase[$add($itemActivityIdx, 1)])) {
                    return $itemActivitiesBase[$add($itemActivityIdx, 1)]->getAnswer();
                }
                return NULL;

            case 'j2':
                if (isset($itemActivitiesBase[$add($itemActivityIdx, 2)])) {
                    return $itemActivitiesBase[$add($itemActivityIdx, 2)]->getAnswer();
                }
                return NULL;

            case 'j3':
                if (isset($itemActivitiesBase[$add($itemActivityIdx, 3)])) {
                    return $itemActivitiesBase[$add($itemActivityIdx, 3)]->getAnswer();
                }
                return NULL;
        }
    }

}
