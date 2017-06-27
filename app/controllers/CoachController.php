<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/4/17
 * Time: 11:39 AM
 */
class CoachController extends Controller
{
    function login()
    {
        //security checks for page access
        $this->checkAccess('coach', true, true);

        $template = new Template;
        echo $template->render('coach/header.html');
        echo $template->render('coach/login.html');
        echo $template->render('coach/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function loggedin()
    {
        //security checks for page access
        $this->checkAccess('coach', false, false);

        $template = new Template;

        //load coach details
        $coach = $this->getCoachDetails();

        //get total revenue
        $this->f3->set('totalrevenue', $this->getTotalRevenue($coach->id));

        //get per month details
        $this->getSubscribers($coach->id);

        $permonthsubscribers = array();
        $permonthrevenue = array();
        for ($i = 1; $i <= 12; $i++) {
            $count = $this->getPerMonthSubscribers($coach->id, date("Y"), $i)[0]["mcount"];
            $revenue = $this->getPerMonthRevenue($count);
            $permonthsubscribers[] = $count;
            $permonthrevenue[] = $revenue;
        }

        $this->f3->set('permonthsubscribers', $permonthsubscribers);
        $this->f3->set('permonthrevenue', $permonthrevenue);

        echo $template->render('coach/header.html');
        echo $template->render('coach/index.html');
        echo $template->render('coach/footer.html');
    }

    function renderregister()
    {
        //security checks for page access
        $this->checkAccess('coach', true, true);

        $template = new Template;
        echo $template->render('header.html');
        echo $template->render('coach/register.html');
        echo $template->render('footer.html');
        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function register()
    {
        //get values from form
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');
        $password_check = $this->f3->get('POST.passwordcheck');
        $name = $this->f3->get('POST.name');
        $surname = $this->f3->get('POST.surname');

        //check if all fields are completed
        if (!$email or !$name or !$surname or !$password or !$password_check) {
            $this->setErrorStatus('coach_register_all_fields', $this->f3->get('coach_register_error_route'));
        }

        //check if passwords match
        if ($password != $password_check) {
            $this->setErrorStatus('coach_register_no_match_password', $this->f3->get('coach_register_error_route'));
        }

        //check if email is already in use
        $coach = new Coach($this->db);
        $coach->getByEmail($email);

        if ($coach->dry() === false) {
            $this->setErrorStatus('coach_register_email_used', $this->f3->get('coach_register_error_route'));
        }

        //generate password hash
        $hash = $coach->generateHash($password);
        //generate referral id
        $referralid = "0x002".bin2hex(random_bytes(16));

        //save coach and reroute for login
        $coach->registerCoach($name, $surname, $email, $hash, $referralid);
        $this->f3->reroute($this->f3->get('coach_default_route'));
    }

    function authenticate()
    {
        //get values from form
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');

        //check if email is registered
        $coach = new Coach($this->db);
        $coach->getByEmail($email);

        //if no coach found with email reroute with error
        if ($coach->dry()) {
            $this->setErrorStatus('coach_login_error', $this->f3->get('coach_default_route'));
        }

        //if coach if disabled deny login
        $coach->getIfEnabled($email);
        if ($coach->dry()) {
            $this->setErrorStatus('coach_login_disabled_error', $this->f3->get('coach_default_route'));
        }

        //check if password is ok and login or reroute with error
        if (hash_equals($coach->password, crypt($password, $coach->password))) {
            $this->f3->set('SESSION.cid', $coach->id);
            $this->f3->set('SESSION.email', $coach->email);
            $this->f3->set('SESSION.lvl', 'coach');
            $this->f3->set('SESSION.intime', time());
            $this->f3->reroute($this->f3->get('coach_default_route'));
        } else {
            $this->setErrorStatus('coach_login_error', $this->f3->get('coach_default_route'));
        }
    }

    //load current coach details
    function getCoachDetails()
    {
        $coach = new Coach($this->db);
        $coach->getByEmail($this->f3->get('SESSION.email'));
        return $this->f3->set('coach', $coach);
    }

    //get total subscribers for current coach
    function getSubscribers($cid)
    {
        $Follower = new Follower($this->db);
        $userCount = $Follower->getFollowerByCoachId($cid);
        return $this->f3->set('userstocoach', $userCount);
    }

    //get total revenue of coach
    function getTotalRevenue($cid)
    {
        $Follower = new Follower($this->db);
        $totalUsers = $Follower->getFollowerByCoachId($cid);
        $revenue = $totalUsers * $this->f3->get('appRevenuePercent') * $this->f3->get('appRevenueValue');
        return $revenue;
    }

    //get per month subscribers of coach
    function getPerMonthSubscribers($cid, $year, $month)
    {
        $Follower = new Follower($this->db);
        $mcount = $Follower->getMonthlyUsersByCoachId($cid, $year, $month);
        return $this->f3->set('month_' . $month, $mcount);
    }

    //get per month revenue
    function getPerMonthRevenue($followers)
    {
        $revenue = $followers * $this->f3->get('appRevenuePercent') * $this->f3->get('appRevenueValue');
        return $revenue;
    }
}