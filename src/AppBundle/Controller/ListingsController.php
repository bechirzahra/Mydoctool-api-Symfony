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
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations as Rest;

use JMS\Serializer\SerializationContext;

use AppBundle\Entity\Listing;
use AppBundle\Entity\Item;
use AppBundle\Entity\Question;
use AppBundle\Entity\Notice;
use AppBundle\Entity\Task;
use AppBundle\Entity\Document;


use AppBundle\Form\Type\ListingFormType;

/**
 * @RouteResource("Listing")
 */
class ListingsController extends FOSRestController
{
    public function optionsAction()
    {}

    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('AppBundle:Listing');

        $data = $repository->findAll();

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }


    /**
    * @Get("/listings/{slug}/load-data/{projectSlug}")
    */
    public function loadListingDataAction($slug, $projectSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('AppBundle:Listing');

        // $project = $em->getRepository('AppBundle:Project')->findOneBySlug($projectSlug);
        // if (!$project) {
        //     throw new NotFoundHttpException('Project not found.');
        // }

        $listing = $repository->findOneBySlug($slug);
        // $fieldTypes = $listing->getFieldTypes();
        // $answers = $em->getRepository('AppBundle:Answer')->findByProject($project);
        // $documents = $em->getRepository('AppBundle:Document')->findAvailable($project);

        $ret = array(
            'listing' => $listing,
            // 'fieldTypes' => $fieldTypes,
            // 'resources' => $listing->getResources(),
            // 'answers' => $answers,
            // 'documents' => $documents,
        );

        $view = $this->view($ret, 200);

        return $this->handleView($view);
    }

    /**
    * @POST("/listings/create-empty")
    */
    public function createEmptyListingAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $listing = new Listing($currentUser);
        $form = $this->getForm();
        $form->setData($listing);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($listing);
            $em->flush();
            $view = $this->view($listing, 200);
        } else {
            $view = $this->view($form);
        }


        return $this->handleView($view);
    }

    /**
    * @POST("/listings/{listingSlug}/save")
    * @Rest\View(serializerEnableMaxDepthChecks=true)
    */
    public function saveListingAction(Request $request, $listingSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $listing = $em->getRepository('AppBundle:Listing')->findOneBySlug($listingSlug);

        if (!$listing) {
            throw new NotFoundHttpException('No Listing found');
        }

        $form = $this->getForm($listing);
        $form->bind($request);

        if ($form->isValid()) {

            $itemManager = $this->get('mdt.item_manager');
            $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

            $currentUser = $this->get('security.token_storage')->getToken()->getUser();

            // First, we delete old items if there's any
            $removedItems = $request->request->get('removedItems');
            if (isset($removedItems)) {
                foreach ($removedItems as $itemSlug) {
                    $oldItem = $em->getRepository('AppBundle:Item')->findOneBySlug($itemSlug);
                    if ($oldItem !== null) {
                        $listing->removeItem($oldItem);
                        $em->remove($oldItem);
                    }
                }
            }

            // Now, we add items
            $items = json_decode($request->request->get('items'));

            $documents = $request->files->get('documents');
            $documentsName = $request->request->get('documents');

            $newItems = [];

            foreach ($items as $key => $rawItem) {
                // Is the Item a new Item?
                $existingItem = $em->getRepository('AppBundle:Item')->findOneBySlug($rawItem->slug);

                // If the item does not exists, we create one and persist it
                if (!$existingItem) {
                    $existingItem = $itemManager->createItemFromData($rawItem);

                    $listing->addItem($existingItem);
                    $em->persist($existingItem);

                    $newItems[$rawItem->slug] = $existingItem->getSlug();
                }
                // Else, we update it
                else {
                    $existingItem = $itemManager->updateItemFromData($existingItem, $rawItem);
                }

                // Remove deleted documents
                $docs = isset($documentsName[$rawItem->slug]) ? $documentsName[$rawItem->slug] : [];
                foreach ($existingItem->getDocuments() as $doc) {
                    $found = false;
                    foreach ($docs as $v) {
                        if ($v['slug'] == $doc->getSlug()) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found)
                        $existingItem->removeDocument($doc);
                }

                // Handle documents upload
                if (isset($documents[$rawItem->slug]) && count($documents[$rawItem->slug]) > 0) {

                    foreach ($documents[$rawItem->slug] as $k => $doc) {
                        $docFile = $doc['file'];
                        $docName = $documentsName[$rawItem->slug][$k]['name'];

                        $createdDoc = new Document($docName);
                        $createdDoc->setFile($docFile);
                        $createdDoc->setUser($currentUser);
                        $createdDoc->setFilename();

                        // Take care of the upload of the file
                        $fs->write(
                            $createdDoc->getFullPath(),
                            file_get_contents($createdDoc->file->getPathname())
                        );

                        $em->persist($createdDoc);

                        // Attach it to the items
                        $existingItem->addDocument($createdDoc);
                    }
                }
            }

            // We should re-foreach on items to be sure that alerts to not contain
            // JS-generated uid instead of valid slug
            foreach ($listing->getItems() as $item) {
                $alerts = $item->getAlerts();
                foreach ($alerts as $i => $alert) {
                    $conditions = $alert->conditions;

                    foreach ($conditions as $j => $condition) {
                        if (isset($condition->base->slug) && !preg_match('/^ite/', $condition->base->slug)) {
                            $condition->base->slug = $newItems[$condition->base->slug];
                            $conditions[$j] = $condition;
                        }
                    }

                    $alert->conditions = $conditions;
                }
                $item->setAlerts($alerts);
            }

            $published = $request->request->get('published') == "true" ? true : false;
            $listing->setPublished($published);

            $em->flush();

            $ret = array(
                'listing' => $listing,
                'items' => $listing->getItems(),
            );

            $view = $this->view($ret);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    /**
    * @POST("/listings/{listingSlug}/duplicate")
    */
    public function duplicateListingAction(Request $request, $listingSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $listing = $em->getRepository('AppBundle:Listing')->findOneBySlug($listingSlug);

        if (!$listing) {
            throw new NotFoundHttpException('No Listing found');
        }

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        if ($listing->getOwner() !== $currentUser && !$listing->getIsTemplate()) {
            throw new AccessDeniedException('This protocol is not yours');
        }

        $newListing = new Listing($currentUser, $listing->getCategory());

        $toCopy = ['color', 'text', 'durationValue', 'durationUnit'];
        foreach ($toCopy as $k => $v) {
            $getter = 'get' . ucfirst($v);
            $setter = 'set' . ucfirst($v);
            $newListing->$setter($listing->$getter());
        }
        $newListing->setName($listing->getName() . ' (copy)');

        /*
            Duplicate every Item

            We duplicate the item and its document.
            We should also change, in its Alerts, the slugs of the new items
        */

        // Array (key, val) with the key: the old item slug, the value: the new item slug
        $itemSlugs = array();
        $newItems = array();
        foreach ($listing->getItems() as $item) {
            if ($item->getType() === Item::TYPE_QUESTION) {
                $newItem = new Question($item->getName(), $item->getText(), $item->getOrderC());

                $newItem->setQuestionType($item->getQuestionType());
                $newItem->setUnit($item->getUnit());
                $newItem->setTextAnswerShort($item->getTextAnswerShort());
                $newItem->setIcon($item->getIcon());
                $newItem->setMin($item->getMin());
                $newItem->setMax($item->getMax());

            } else if ($item->getType() === Item::TYPE_NOTICE) {
                $newItem = new Notice($item->getName(), $item->getText(), $item->getOrderC());
            } else {
                $newItem = new Task($item->getName(), $item->getText(), $item->getOrderC());

                $newItem->setRepeated($item->getRepeated());
            }

            $newItem->setFrequencies($item->getFrequencies());
            $newItem->setAlerts($item->getAlerts());

            // Duplicate every Document
            foreach ($item->getDocuments() as $doc) {
                $newDoc = new Document($doc->getName());
                $newDoc->setForcedFilename($doc->getFilename());

                $currentUser->addDocument($newDoc);
                $newItem->addDocument($newDoc);

                $em->persist($newDoc);
            }

            $em->persist($newItem);
            $newListing->addItem($newItem);

            $itemSlugs[$item->getSlug()] = $newItem->getSlug();
            $newItems[] = $newItem;
        }

        // Now, for every new Item, we should update its alerts
        foreach ($newItems as $k => $newItem) {
            $alerts = $newItem->getAlerts();

            foreach ($alerts as $kk => $alert) {
                if (isset($alert['conditions'])) {
                    $conditions = $alert['conditions'];

                    foreach ($conditions as $kkk => $condition) {
                        // If the condition is on another item
                        if (isset($condition['base'])) {
                            $oldSlug = $condition['base']['slug'];
                            $conditions[$kkk]['base']['slug'] = $itemSlugs[$oldSlug];
                        }
                    }
                    $alerts[$kk]['conditions'] = $conditions;
                }
            }
            $newItems[$k]->setAlerts($alerts);
        }

        $em->persist($newListing);
        $em->flush();

        $ret = array(
            'listing' => $newListing,
            'items' => $newListing->getItems()
        );

        $view = $this->view($ret);

        return $this->handleView($view);
    }

    /**
    * @POST("/listings/{listingSlug}/toggle-template")
    */
    public function toggleTemplateListingAction(Request $request, $listingSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $listing = $em->getRepository('AppBundle:Listing')->findOneBySlug($listingSlug);
        if (!$listing) {
            throw new NotFoundHttpException('no listing');
        }

        $listing->setIsTemplate(!$listing->getIsTemplate());

        $em->flush();
        $view = $this->view($listing, 200);

        return $this->handleView($view);
    }

    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $listing = $em->getRepository('AppBundle:Listing')->findOneBySlug($slug);
        if (!$listing) {
            throw new NotFoundHttpException('Listing not found.');
        }

        try {
            $em->remove($listing);
            $em->flush();

            $ret = array('slug' => $slug);
            $view = $this->view($ret);
        } catch (\Exception $e) {
            $ret = array('errors' => $e->getMessage());
            $view = $this->view($ret, 400);
        }

        return $this->handleView($view);
    }

    protected function getForm($listing = null, $method = 'POST')
    {
        $em = $this->getDoctrine()->getEntityManager();
        return $this->createForm(
            new ListingFormType($em), $listing, array('method' => $method)
        );
    }

}
