<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/3/17
 * Time: 10:46 PM
 */
class LandingController extends Controller
{
    function render()
    {
        $template = new Template;
        echo $template->render('header.html');
        echo $template->render('index.html');
        echo $template->render('footer.html');

        //clear error status after display
        $this->f3->set('SESSION.haserror', '');
    }

    function SignupUser()
    {
        $useremail = $this->f3->get('POST.email');

        //set as new user initially
        $this->f3->set('new_user', true);

        //get details about the user
        $User = new User($this->db);
        $User->getByEmail($useremail);

        //check if valid email is entered
        $audit = \Audit::instance();
        if ($audit->email($useremail, TRUE) === FALSE) {
            $this->setErrorStatus('follow_register_invalid_mail', $this->f3->get('url_default'));
        }

        //check if provided email is in the wait list, else register new account
        if ($audit->email($User->email, TRUE) === FALSE) {
            $User->registerUser($useremail, "");
            $User->getByEmail($useremail);
            //update status as already registered user
            $this->f3->set('new_user', false);
        }

        //get user waiting rank
        $this->f3->set('waitrank', $User->getUserWaitPosition($User->id)-1);
        $this->f3->set('uniqueurl', $User->referral);

        //send email
        $this->sendSmtpEmail('user_register', null, null, $User->email, $User->referral, null, $this->f3->get('waitrank'));

        $template = new Template;
        echo $template->render('header.html');
        echo $template->render('user/header.html');
        echo $template->render('user/index.html');
        echo $template->render('user/footer.html');
        echo $template->render('footer.html');
    }

    function Subscribe()
    {
        $Coach = new Coach($this->db);
        $User = new User($this->db);
        $template = new Template;

        $uurl = $this->f3->get('PARAMS.uurl');
        if (substr($uurl, 0, 5) == "0x001") {
            //check if valid user
            if ($User->getByReferral($uurl) == 1) {
                //save current url for error handling
                $current_url = $this->f3->get('PATH');
                $this->f3->set('currenturl', $current_url);

                //save current uurl for user id
                $this->f3->set('userid', $uurl);

                echo $template->render('header.html');
                echo $template->render('subscribe/user.html');
                echo $template->render('footer.html');

                //clear error status after display
                $this->f3->set('SESSION.haserror', '');
            } else {
                $this->setErrorStatus('error_uuid_user_not_found', $this->f3->get('url_default'));
            }
        } elseif (substr($uurl, 0, 5) == "0x002") {
            //check if valid coach
            if ($Coach->getByReferral($uurl) == 1) {
                //save current url for error handling
                $current_url = $this->f3->get('PATH');
                $this->f3->set('currenturl', $current_url);

                //save current uurl for user id
                $this->f3->set('coachid', $uurl);

                echo $template->render('header.html');
                echo $template->render('subscribe/coach.html');
                echo $template->render('footer.html');

                //clear error status after display
                $this->f3->set('SESSION.haserror', '');
            } else {
                $this->setErrorStatus('error_uuid_coach_not_found', $this->f3->get('url_default'));
            }
        }
    }

    function SubcribeUser()
    {
        $subscribe_email = $this->f3->get('POST.email');
        $subscribe_url = $this->f3->get('POST.url');
        $subscribe_userid = $this->f3->get('POST.userid');

        $User = new User($this->db);
        $Follower = new Follower($this->db);

        //check if valid email
        $audit = \Audit::instance();
        if ($audit->email($subscribe_email, TRUE) === FALSE) {
            $this->setErrorStatus('follow_register_invalid_mail', $subscribe_url);
        }

        //get user id
        $userid = $User->getIdFromReferral($subscribe_userid);

        //check if already subscribed to someone
        /*
        if ($Follower->getIfFollowing($subscribe_email) == 1) {
            $this->setErrorStatus('already_subscribe_user_message', $subscribe_url);
        }
        */

        //check if subscription was successful
        $lastid = $Follower->registerFollower($subscribe_email, $userid, null);
        if (!$lastid) {
            $this->setErrorStatus('error_generic', $subscribe_url);
        } else {
            $this->setErrorStatus('ok_subscribe_user_message', $this->f3->get('url_default'));
        }
    }


    function SubcribeCoach()
    {
        $subscribe_email = $this->f3->get('POST.email');
        $subscribe_url = $this->f3->get('POST.url');
        $subscribe_coachid = $this->f3->get('POST.coachid');

        $Coach = new Coach($this->db);
        $Follower = new Follower($this->db);

        //check if valid email
        $audit = \Audit::instance();
        if ($audit->email($this->f3->get('POST.email'), TRUE) === FALSE) {
            $this->setErrorStatus('follow_register_invalid_mail', $this->f3->get('url_default'));
        }

        //get coach id
        $coachid = $Coach->getIdFromReferral($subscribe_coachid);

        //check if already subsribed to any coach
        $lastid = $Follower->registerFollower($subscribe_email, null, $coachid);
        if (!$lastid) {
            $this->setErrorStatus('error_generic', $subscribe_url);
        } else {
            $this->setErrorStatus('ok_subscribe_user_message', $this->f3->get('url_default'));
        }
    }

    function renderAbout()
    {
        //the about page
    }
}
