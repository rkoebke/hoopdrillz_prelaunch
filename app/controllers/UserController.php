<?php
/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/4/17
 * Time: 1:32 PM
 */

class UserController extends Controller {
    function renderSubscribe() {
        //security checks for page access
        $this->checkAccess('user',true,true);

        $template = new Template;
        echo $template->render('header.html');
        echo $template->render('user/subscribe.html');
        echo $template->render('footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function renderFollow() {
        //security checks for page access
        $this->checkAccess('user',true,true);

        $template = new Template;
        echo $template->render('header.html');
        echo $template->render('user/follow.html');
        echo $template->render('footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function login() {
        //security checks for page access
        $this->checkAccess('user',true,true);


        $template = new Template;
        echo $template->render('header.html');
        echo $template->render('user/login.html');
        echo $template->render('footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function loggedin() {
        //security checks for page access
        $this->checkAccess('user',false,false);

        $template = new Template;
        $user = new User($this->db);

        //get user details
        $user->getByEmail($this->f3->get('SESSION.email'));
        $this->f3->set('user', $user);

        //get user followers
        $this->f3->set('followers', $user->getUserFollowers($this->f3->get('SESSION.cid'))[0]);

        //get user waiting position
        $this->f3->set('waitrank', $user->getUserWaitPosition($this->f3->get('SESSION.cid'))[0]);

        echo $template->render('header.html');
        echo $template->render('user/user.html');
        echo $template->render('footer.html');
    }

    function authenticate() {
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');

        $user = new User($this->db);
        $user->getByEmail($email);

        if($user->dry()) {
            $this->setErrorStatus('user_login_error', $this->f3->get('user_default_route'));
        }

        if(hash_equals($user->password, crypt($password, $user->password))) {
            $this->f3->set('SESSION.cid', $user->id);
            $this->f3->set('SESSION.email', $user->email);
            $this->f3->set('SESSION.lvl', 'user');
            $this->f3->set('SESSION.intime', time());
            $this->f3->reroute('/user/'.$user->id);
        } else {
            $this->setErrorStatus('user_login_error', $this->f3->get('user_default_route'));
        }
    }

    function register() {
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');
        $password_check = $this->f3->get('POST.passwordcheck');
        $name = $this->f3->get('POST.name');
        $surname = $this->f3->get('POST.surname');
        $coach_referral = $this->f3->get('POST.coach_referral');

        //check if all fields are completed
        if (!$email || !$name || !$surname || !$password || !$password_check || !$coach_referral){
            $this->setErrorStatus('subscribe_error_all_fields', $this->f3->get('subscribe_default_route'));
        }

        //check if passwords are identical
        if ($password != $password_check) {
            $this->setErrorStatus('subscribe_error_passwords', $this->f3->get('subscribe_default_route'));
        }

        //check for coach referral
        $coach = new Coach($this->db);
        $coach->getByReferral($coach_referral);

        if ($coach->dry()) {
            $this->setErrorStatus('subscribe_error_invalid_referral', $this->f3->get('subscribe_default_route'));
        } else {
            $coach_id = $coach->id;
        }

        //check for valid email
        $audit = \Audit::instance();
        if ($audit->email($email, TRUE) === FALSE) {
            $this->setErrorStatus('subscribe_error_invalid_mail', $this->f3->get('subscribe_default_route'));
        }

        //check if email is already registered
        $user = new User($this->db);
        $user->getByEmail($email);

        if (!$user->dry()) {
            $this->setErrorStatus('subscribe_error_already_follow', $this->f3->get('subscribe_default_route'));
        }

        //generate password hash
        $hash = $user->generateHash($password);

        //generate user referral
        $referralid = "0x001".bin2hex(random_bytes(16));


        $user->registerUser($name, $surname, $email, $hash, $coach_id, $referralid);
        $this->f3->reroute('/user');
    }
}