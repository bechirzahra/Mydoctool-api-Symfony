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

class CheckItemActivityCommand extends ContainerAwareCommand
{

    private $em;
    private $mailer;

    protected function configure()
    {
        $this
            ->setName('mdt:itemactivity:check')
            ->setDescription('Every day task to generate new Item Activities')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->mailer = $this->getContainer()->get('mdt.mailer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting to check for new ItemActivities:");

        // First, we get every Patient that have at least one Listing

        $userListings = $this->em->getRepository('AppBundle:UserListing')->findAll();

        $subject = 'MyDocTool - Vous avez de nouveaux messages ';
        $fromEmail = array('support@mydoctool.com' => 'MyDocTool');
        $templateName = 'AppBundle:Emails:newActivity.html.twig';

        $usersToSendEmail = array();

        foreach ($userListings as $userListing) {
            $hasNewActivity = false;
            $count = array(
                'tasks' => 0,
                'questions' => 0,
                'notices' => 0,
            );

            $user = $userListing->getPatient();
            $listing = $userListing->getListing();
            $listingStart = $userListing->getCreatedAt();

            $output->writeln('User ... ' . $user->getPrintableName() . ' ... Listing ...' . $listing->getName());

            $today = (new \DateTime())->setTime(0,0);

            $durationValue = $listing->getDurationValue();
            $durationUnit = $listing->getDurationUnit();

            $activeListing = false;
            if ($durationUnit === Listing::UNIT_END) {
                $activeListing = true;
            } else if ($durationUnit !== null && $durationValue !== null) {
                $dateInterval = $this->createDateInterval($durationValue, $durationUnit);
                $comparingDate = clone $listingStart;
                $comparingDate->add($dateInterval);
                if ($comparingDate >= $today) {
                    $activeListing = true;
                }
            }

            // Now, we have an Active Listing
            // We should check for every of its items

            if ($activeListing) {
                $items = $listing->getItems();
                foreach ($items as $item) {
                    $frequencies = $item->getFrequencies();

                    foreach ($frequencies as $k => $frequency) {
                        $freqDateInterval = $this->createDateInterval($frequency['dateStart']['value'], $frequency['dateStart']['unit']);
                        $comparingStartDate = clone $listingStart;
                        $comparingStartDate->add($freqDateInterval);
                        $comparingStartDate->setTime(0, 0);

                        // If the Frequency has started
                        if ($comparingStartDate <= $today) {
                            $activeFreq = false;
                            // We should now check if it hasn't ended
                            // If it should go until the end of the protocol,
                            // add it no matter what.
                            if ($frequency['duration']['type'] == Item::DURATION_TYPE_END) {
                                $activeFreq = true;
                            }
                            // If it's a ONCE frequency, we should add it no matter what the timeframe is
                            else if ($frequency['frequency'] == Item::FREQUENCY_ONCE) {
                                $activeFreq = true;
                            }
                            // Else, just check if we're good
                            else {
                                $freqDateIntervalBis = $this->createDateInterval($frequency['duration']['value'], $frequency['duration']['unit']);
                                $comparingEndDate = clone $comparingStartDate;
                                $comparingEndDate->add($freqDateIntervalBis);


                                if ($comparingEndDate >= $today) {
                                    $activeFreq = true;
                                }
                            }

                            // If the Frequence is active (right timeframe)
                            if ($activeFreq) {
                                // Last check, we should check what is the Frequency of the
                                // ItemActivity and if we should recreate one.
                                $itemActivities = $this->em->getRepository('AppBundle:ItemActivity')->findBy(array(
                                    'user' => $user,
                                    'item' => $item
                                ));
                                // We sort those Item Activities by date (the closest Date first)
                                if (count($itemActivities) > 1) {
                                    usort($itemActivities, function($iA, $iB) {
                                        if ($iA->getCreatedAt() === $iB->getCreatedAt()) {
                                            return 0;
                                        }
                                        return $iA->getCreatedAt() < $iB->getCreatedAt() ? 1 : -1;
                                    });
                                }

                                $cItemActivities = count($itemActivities);
                                $generate = false;
                                if ($cItemActivities === 0) {
                                    $generate = true;
                                } else {
                                    $lastItemActivity = $itemActivities[0];
                                    $lastItemActivityDate = $lastItemActivity->getCreatedAt();

                                    $comparingIADate = clone $lastItemActivityDate;
                                    $comparingIADate->setTime(0, 0, 0);
                                    if ($today >= $comparingIADate) {
                                        $comparingIADate = $comparingIADate->diff($today);

                                        switch ($frequency['frequency']) {
                                            case Item::FREQUENCY_EVERY_DAY:
                                                if ($comparingIADate->d >= 1 && $comparingIADate->m === 0 && $comparingIADate->y === 0) {
                                                    $generate = true;
                                                }
                                                break;

                                            case Item::FREQUENCY_EVERY_TWO_DAYS:
                                                if ($comparingIADate->d >= 2 && $comparingIADate->m === 0 && $comparingIADate->y === 0) {
                                                    $generate = true;
                                                }
                                                break;

                                            case Item::FREQUENCY_EVERY_THREE_DAYS:
                                                if ($comparingIADate->d >= 3 && $comparingIADate->m === 0 && $comparingIADate->y === 0) {
                                                    $generate = true;
                                                }
                                                break;

                                            case Item::FREQUENCY_EVERY_WEEK:
                                                if ($comparingIADate->d >= 7 && $comparingIADate->m === 0 && $comparingIADate->y === 0) {
                                                    $generate = true;
                                                }
                                                break;

                                            case Item::FREQUENCY_EVERY_MONTH:
                                                if ($comparingIADate->d === 0 && $comparingIADate->m >= 1 && $comparingIADate->y === 0) {
                                                    $generate = true;
                                                }
                                                break;

                                            case Item::FREQUENCY_TWICE_A_MONTH:
                                                if ($comparingIADate->d >= 15 && $comparingIADate->m === 0 && $comparingIADate->y === 0) {
                                                    $generate = true;
                                                }
                                                break;
                                        };
                                    }
                                }

                                if ($generate) {
                                    $newIA = new ItemActivity($user, $item);
                                    $hasNewActivity = true;
                                    $this->em->persist($newIA);

                                    if ($item->getType() === Item::TYPE_NOTICE) {
                                        $count['notices']++;
                                    } else if ($item->getType() === Item::TYPE_TASK) {
                                        $count['tasks']++;
                                    } else {
                                        $count['questions']++;
                                    }

                                    $output->writeln('New Item Activity for user: ' . $user->getPrintableName() . '(' .$user->getEmail() . ')');
                                }
                            }
                        }
                    }
                }
            }

            // We should also check if the user has a not-done repeated task
            $itemActivitiesN = $this->em->getRepository('AppBundle:ItemActivity')->findCByUserNotDone($user);

            foreach ($itemActivitiesN as $iA) {
                if (!$iA->done()) {
                    $tItem = $iA->getItem();
                    if ($tItem->printableType() === 'task' && $tItem->getRepeated()) {
                        $hasNewActivity = true;
                        $count['tasks']++;
                    }
                }
            }

            if ($hasNewActivity) {
                if (isset($usersToSendEmail[$user->getId()])) {
                    $usersToSendEmail[$user->getId()]['count']['notices'] += $count['notices'];
                    $usersToSendEmail[$user->getId()]['count']['tasks'] += $count['tasks'];
                    $usersToSendEmail[$user->getId()]['count']['questions'] += $count['questions'];
                } else {
                    $usersToSendEmail[$user->getId()] = array('user' => $user, 'count' => $count);
                }
            }
        }

        foreach ($usersToSendEmail as $data) {
            $user = $data['user'];
            $count = $data['count'];

            $templateArgs = array('user' => $user, 'count' => $count);
            $this->mailer->sendEmail($subject, $fromEmail, $user->getEmail(), $templateName, $templateArgs);
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
        } else if ($unit == Listing::UNIT_YEAR) {
            $dateInterval = new \DateInterval("P" . $value . "Y");
        } else {
            $dateInterval = new \DateInterval("P0D");
        }

        return $dateInterval;
    }
}
