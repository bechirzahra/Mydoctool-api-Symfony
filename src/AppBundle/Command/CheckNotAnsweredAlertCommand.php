<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\UserListing;
use AppBundle\Entity\Listing;
use AppBundle\Entity\Item;
use AppBundle\Entity\ItemActivity;
use AppBundle\Entity\Alert;
use AppBundle\Entity\Message;

class CheckNotAnsweredAlertCommand extends ContainerAwareCommand
{

    private $em;

    protected function configure()
    {
        $this
            ->setName('mdt:alert:check')
            ->setDescription('Every day task to generate new Item Activities')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting to check for Tasks / Text Alerts");

        $items = $this->em->getRepository('AppBundle:Item')->findTasksAndTexts();
        $today = (new \DateTime())->setTime(0,0);

        foreach ($items as $item) {
            if ($item->getAlerts() !== null && count($item->getAlerts()) > 0) {

                $itemStart = $item->getCreatedAt();

                foreach ($item->getAlerts() as $alert) {
                    $condition = $alert['conditions'][0];
                    $daysToWait = $condition['answer']['value'];
                    if ($daysToWait === '') {
                        $daysToWait = 0;
                    }
                    $dateInterval = $this->createDateInterval($daysToWait, Listing::UNIT_DAY);

                    // Now, we get every itemActivity of this item that has not been done
                    $itemActivities = $this->em->getRepository('AppBundle:ItemActivity')->findCByItemSlugNotDone($item->getSlug());

                    foreach ($itemActivities as $iA) {

                        $comparingDate = clone $iA->getCreatedAt();
                        $comparingDate->add($dateInterval)->setTime(0, 0);

                        if ($comparingDate->diff($today)->days === 0) {
                            $newAlert = new Alert($iA, $alert['patientMessage']['send'], $alert['doctorMessage']['send']);
                            $newAlert->setAlertUid($alert['uid']);

                            $patient = $iA->getUser();
                            $doctor = $item->getUser();

                            if ($alert['patientMessage']['send']) {
                                $patientMessage = new Message();
                                $patientMessage->setFromUser($doctor);
                                $patientMessage->setToUser($patient);
                                $patientMessage->setText($alert['patientMessage']['message']);
                                $this->em->persist($patientMessage);

                                $newMessages[] = $patientMessage;
                            }

                            if ($alert['doctorMessage']['send']) {
                                $doctorMessage = new Message();
                                $doctorMessage->setFromUser($patient);
                                $doctorMessage->setToUser($doctor);
                                $doctorMessage->setText($alert['doctorMessage']['message']);
                                $this->em->persist($doctorMessage);
                            }

                            $output->writeln('New Alert for ... ' . $patient->getPrintableName() . ' (' . $patient->getEmail() . ')');
                            $this->em->persist($newAlert);
                        }
                    }
                }
            }
        }

        $this->em->flush();

        $output->writeln("Done.");
    }

    public function createDateInterval($value, $unit)
    {
        if ($unit == Listing::UNIT_DAY) {
            $dateInterval = new \DateInterval("P" . $value . "D");
        } else if ($unit == Listing::UNIT_WEEK) {
            $dateInterval = new \DateInterval("P" . $value . "W");
        } else if ($unit == Listing::UNIT_MONTH) {
            $dateInterval = new \DateInterval("P" . $value . "M");
        } else {
            $dateInterval = new \DateInterval("P0D");
        }

        return $dateInterval;
    }
}