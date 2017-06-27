<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/6/17
 * Time: 3:33 AM
 */
class AdminController extends Controller
{

    function login()
    {
        //security checks for page access
        $this->checkAccess('admin',true,true);

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/login.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function register()
    {
        //get values from form
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');
        $password_check = $this->f3->get('POST.passcheck');
        $name = $this->f3->get('POST.name');
        $surname = $this->f3->get('POST.surname');

        //check if all fields are completed
        if (!$email or !$name or !$surname or !$password or !$password_check) {
            $this->setErrorStatus('admin_add_error_fields', $this->f3->get('admin_add_route_error'));
        }

        //check if passwords match
        if ($password != $password_check) {
            $this->setErrorStatus('admin_add_error_password', $this->f3->get('admin_add_route_error'));
        }

        //check for valid email
        $audit = \Audit::instance();
        if ($audit->email($email, TRUE) === FALSE) {
            $this->setErrorStatus('admin_add_error_invalid_mail', $this->f3->get('admin_add_route_error'));
        }

        //check if email is already in use
        $admin = new Admin($this->db);
        $admin->getByEmail($email);

        if ($admin->dry() === false) {
            $this->setErrorStatus('admin_add_error_email', $this->f3->get('admin_add_route_error'));
        }

        //generate password hash
        $hash = $admin->generateHash($password);

        //save coach and reroute for login
        $admin->registerAdmin($name, $surname, $email, $hash);
        $this->f3->reroute($this->f3->get('admin_default_route'));
    }

    function auth()
    {
        //get values from form
        $email = $this->f3->get('POST.email');
        $password = $this->f3->get('POST.password');

        //check if email is registered
        $admin = new Admin($this->db);
        $admin->getByEmail($email);

        //if no admin found with email reroute with error
        if ($admin->dry()) {
            $this->setErrorStatus('admin_login_error', $this->f3->get('admin_default_route'));
        }

        //check if password is ok and login or reroute with error
        if (hash_equals($admin->password, crypt($password, $admin->password))) {
            $this->f3->set('SESSION.cid', $admin->id);
            $this->f3->set('SESSION.email', $admin->email);
            $this->f3->set('SESSION.lvl', 'admin');
            $this->f3->set('SESSION.intime', time());
            $this->f3->reroute($this->f3->get('admin_default_route'));
        } else {
            $this->setErrorStatus('admin_login_error', $this->f3->get('admin_default_route'));
        }
    }

    function loggedin()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $admin = new Admin($this->db);
        //load admin details
        $this->f3->set('admin', $admin->getAdminDetails());
        //get all admins
        $this->f3->set('admintotal', $admin->getCountAllAdmins()[0]);


        $coaches = new Coach($this->db);
        //load all enabled and disabled coaches
        $this->f3->set('totalenabledcoaches', $coaches->getCountEnabledCoaches()[0]);
        $this->f3->set('totaldisabledcoaches', $coaches->getCountDisabledCoaches()[0]);


        $users = new User($this->db);
        //load total users
        $this->f3->set('totalusers', $users->getCountAllUsers()[0]);


        $followers = new Follower($this->db);
        //load total followers
        $this->f3->set('totalfollowers', $followers->getCountAllFollowers()[0]);


        $template = new Template;
        //render page
        echo $template->render('admin/header.html');
        echo $template->render('admin/index.html');
        echo $template->render('admin/footer.html');
    }

    function renderregister()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $template = new Template;
        echo $template->render('header.html');
        echo $template->render('admin/register.html');
        echo $template->render('footer.html');
        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function renderadminadd()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/adminadd.html');
        echo $template->render('admin/footer.html');
        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function renderCoachAdd() {
        //security checks for page access
        $this->checkAccess('admin',false,false);
        $Admin = new Admin($this->db);

        $this->f3->set('randPassword', $Admin->generateRandom());

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/coachadd.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function coachAddSave() {
        $coach_name = $this->f3->get('POST.name');
        $coach_surname = $this->f3->get('POST.surname');
        $coach_email = $this->f3->get('POST.email');
        $coach_pass = $this->f3->get('POST.password');

        $audit = \Audit::instance();
        if ($audit->email($coach_email, TRUE) === FALSE) {
            $this->setErrorStatus('admin_coach_add_msg_mail_err', $this->f3->get('admin_coach_add_url'));
        }

        if (empty($coach_name) && empty($coach_surname) && empty($coach_email)) {
            $this->setErrorStatus('admin_coach_add_mgs_add_error', $this->f3->get('admin_coach_add_url'));
        }

        $Coach = new Coach($this->db);

        //check if email is already used
        if ($Coach->getIfEmailIsUsed($coach_email) == 1) {
            $this->setErrorStatus('admin_coach_add_msg_umail_err', $this->f3->get('admin_coach_add_url'));
        }

        //check if save is successful
        $lastid = $Coach->registerCoach($coach_name, $coach_surname, $coach_email, $coach_pass);
        //check if subscription was successful
        if (!$lastid) {
            $this->setErrorStatus('admin_coach_add_msg_error', $this->f3->get('admin_coach_add_url'));
        } else {
            $this->setErrorStatus('admin_coach_add_msg_success', $this->f3->get('admin_loggedin_route_list_coach'));
        }
    }


    // to be deleted
    function renderlistcoaches()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $coaches = new Coach($this->db);
        $this->f3->set('coaches', $coaches->getAllCoaches());

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/coachlist.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function renderCoachTools()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $coaches = new Coach($this->db);
        $this->f3->set('coaches', $coaches->getAllCoaches());

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/coachlist.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function renderEmailTools()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $Admin = new Admin($this->db);

        $this->f3->set('total_coach_email', $Admin->getTotalCoachEmails()[0]);
        $this->f3->set('total_users_email', $Admin->getTotalUserEmails()[0]);
        $this->f3->set('total_followers_email', $Admin->getTotalFollowerEmails()[0]);

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/emaillist.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function exportEmailCoach()
    {
        $this->checkAccess('admin',false,false);
        $Admin = new Admin($this->db);
        $Admin->array_to_csv_download($Admin->getExportEmail("coach"),"coaches.csv",",");
    }

    function exportEmailUser()
    {
        $this->checkAccess('admin',false,false);
        $Admin = new Admin($this->db);
        $Admin->array_to_csv_download($Admin->getExportEmail("user"),"users.csv",",");
    }

    function exportEmailFollower()
    {
        $this->checkAccess('admin',false,false);
        $Admin = new Admin($this->db);
        $Admin->array_to_csv_download($Admin->getExportEmail("follower"),"followers.csv",",");
    }


    function renderAdminTools()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $admins = new Admin($this->db);
        $this->f3->set('admins', $admins->getAllAdmins());


        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/adminlist.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function coachenable()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $cid = $this->f3->get('PARAMS.id');

        $coach = new Admin($this->db);

        if ($coach->coachEnable($cid) === "nok") {
            $this->setErrorStatus('admin_list_coach_enable_error', $this->f3->get('admin_default_coach_list'));
        } else {
            $this->f3->reroute($this->f3->get('admin_default_coach_list'));
        }
    }

    function coachdisable()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $cid = $this->f3->get('PARAMS.id');

        $coach = new Admin($this->db);

        if ($coach->coachDisable($cid) === "nok") {
            $this->setErrorStatus('admin_list_coach_disable_error', $this->f3->get('admin_default_coach_list'));
        } else {
            $this->f3->reroute($this->f3->get('admin_default_coach_list'));
        }
    }

    function coachedit() {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $coachid = $this->f3->get('PARAMS.id');

        $Coach = new Coach($this->db);
        $this->f3->set('coachdetails', $Coach->getById($coachid));

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/coachedit.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function coachEditSave() {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $coach_id = $this->f3->get('POST.coachid');
        $coach_name = $this->f3->get('POST.name');
        $coach_surname = $this->f3->get('POST.surname');
        $coach_email = $this->f3->get('POST.email');
        $coach_pass = $this->f3->get('POST.password');
        $coach_passcheck = $this->f3->get('POST.passcheck');

        if (empty($coach_name) && empty($coach_surname) && empty($coach_email) && empty($coach_pass) && empty($coach_passcheck)) {
            $this->setErrorStatus('admin_coach_edit_msg_no_change', $this->f3->get('admin_coach_edit_url_no_change').$coach_id);
        }

        if ($coach_pass != $coach_passcheck) {
            $this->setErrorStatus('admin_coach_edit_msg_no_pass', $this->f3->get('admin_coach_edit_url_no_change').$coach_id);
        }

        $Coach = new Coach($this->db);

        //check if subscription was successful
        $lastid = $Coach->updateCoach($coach_id, $coach_name, $coach_surname, $coach_email, $coach_pass);
        if (!$lastid) {
            $this->setErrorStatus('error_generic', $this->f3->get('admin_coach_edit_url_no_change').$coach_id);
        } else {
            $this->setErrorStatus('admin_coach_edit_msg_success', $this->f3->get('admin_coach_edit_url_change').$coach_id);
        }
    }

    function adminenable()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $cid = $this->f3->get('PARAMS.id');

        $admin = new Admin($this->db);

        if ($admin->adminEnable($cid) === "nok") {
            $this->setErrorStatus('admin_list_coach_enable_error', $this->f3->get('admin_default_coach_list'));
        } else {
            $this->f3->reroute($this->f3->get('url_admin_tools'));
        }
    }

    function admindisable()
    {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $cid = $this->f3->get('PARAMS.id');

        $admin = new Admin($this->db);

        if ($admin->adminDisable($cid) === "nok") {
            $this->setErrorStatus('admin_list_coach_disable_error', $this->f3->get('admin_default_coach_list'));
        } else {
            $this->f3->reroute($this->f3->get('url_admin_tools'));
        }
    }

    function adminedit() {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $adminid = $this->f3->get('PARAMS.id');

        $Admin = new Admin($this->db);
        $this->f3->set('admindetails', $Admin->getById($adminid));

        $template = new Template;
        echo $template->render('admin/header.html');
        echo $template->render('admin/adminedit.html');
        echo $template->render('admin/footer.html');

        //clear error status
        $this->f3->set('SESSION.haserror', '');
    }

    function adminEditSave() {
        //security checks for page access
        $this->checkAccess('admin',false,false);

        $admin_id = $this->f3->get('POST.adminid');
        $admin_name = $this->f3->get('POST.name');
        $admin_surname = $this->f3->get('POST.surname');
        $admin_email = $this->f3->get('POST.email');
        $admin_pass = $this->f3->get('POST.password');
        $admin_passcheck = $this->f3->get('POST.passcheck');

        if (empty($admin_name) && empty($admin_surname) && empty($admin_email) && empty($admin_pass) && empty($admin_passcheck)) {
            $this->setErrorStatus('admin_edit_save_msg_no_change', $this->f3->get('admin_admin_edit_url_no_change').$admin_id);
        }

        if ($admin_pass != $admin_passcheck) {
            $this->setErrorStatus('admin_edit_save_msg_no_pass', $this->f3->get('admin_admin_edit_url_no_change').$admin_id);
        }

        $Admin = new Admin($this->db);

        //check if subscription was successful
        $lastid = $Admin->updateAdmin($admin_id, $admin_name, $admin_surname, $admin_email, $admin_pass);
        if (!$lastid) {
            $this->setErrorStatus('error_generic', $this->f3->get('admin_admin_edit_url_no_change').$admin_id);
        } else {
            $this->setErrorStatus('admin_edit_save_msg_success', $this->f3->get('admin_admin_edit_url_change'));
        }
    }
}