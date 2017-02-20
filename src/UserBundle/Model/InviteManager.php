<?php

namespace UserBundle\Model;

use UserBundle\Entity\Invite;

class InviteManager {

    private $mailer;
    private $translator;
    private $router;
    private $params;

    function __construct($mailer, $translator, $router, $params) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->router = $router;
        $this->params = $params['front'];
    }

    public function sendInvite($invite)
    {
        $emailData = $this->getEmailDataFromType($invite);
        $subject = $emailData['subject'];
        $template = $emailData['template'];
        $args = $emailData['args'];

        $this->mailer->sendEmail(
            $subject,
            array('site-no-reply@mydoctool.com' => 'MyDocTool'),
            $invite->getToEmail(),
            $template,
            $args
        );

        return true;
    }

    private function getEmailDataFromType($invite)
    {
        $ret = array(
            'subject' => '',
            'template' => null,
            'args' => array()
        );

        switch ($invite->getType()) {
            case Invite::REGISTER_JOIN_ORGANIZATION_MANAGER:
                // @TODO: translator
                $ret['subject'] = "Vous êtes invité à manager l'organisation " . $invite->getFromOrganization()->getName() . " sur MyDocTool";
                $ret['template'] = 'UserBundle:Emails:registerJoinOrganization.html.twig';

                $url = $this->params['register_endpoint'] . '/' . $invite->getSlug();

                $args = array(
                    'type' => 'manager',
                    'organization' => $invite->getFromOrganization(),
                    'url' => $url
                );
                $ret['args'] = $args;
                break;

            case Invite::REGISTER_JOIN_ORGANIZATION_DOCTOR:
                // @TODO: translator
                $ret['subject'] = "Vous êtes invité à rejoindre l'organisation " . $invite->getFromOrganization()->getName() . " sur MyDocTool";
                $ret['template'] = 'UserBundle:Emails:registerJoinOrganization.html.twig';

                $url = $this->params['register_endpoint'] . '/' . $invite->getSlug();

                $args = array(
                    'type' => 'doctor',
                    'organization' => $invite->getFromOrganization(),
                    'url' => $url
                );
                $ret['args'] = $args;
                break;

            case Invite::REGISTER_USER:
                // @TODO: translator
                $ret['subject'] = "Vous êtes invité par le Dr " . $invite->getFromUser()->getPrintableName() . " à rejoindre MyDocTool";
                $ret['template'] = 'UserBundle:Emails:registerUser.html.twig';

                $url = $this->params['register_endpoint'] . '/' . $invite->getSlug();

                $args = array(
                    'type' => 'patient',
                    'user' => $invite->getFromUser(),
                    'url' => $url
                );
                $ret['args'] = $args;
                break;

            default:
                # code...
                break;
        }

        return $ret;
    }

}