<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/3/17
 * Time: 10:46 PM
 */
class Controller
{
    protected $f3;
    protected $db;
    protected $smtp;

    function beforeroute()
    {
    }

    function afterroute()
    {
    }

    function __construct()
    {

        $f3 = Base::instance();
        $this->f3 = $f3;

        $db = new DB\SQL(
            $f3->get('db'),
            $f3->get('dbuser'),
            $f3->get('dbpass'),
            array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
        );
        $this->db = $db;

        /*
        $smtp = new SMTP(
            $f3->get('smtpsrv'),
            $f3->get('smtpport'),
            'tls',
            $f3->get('smtpuser'),
            $f3->get('smtppass')
        );
        $this->smtp = $smtp;
        */
    }

    //set session time based expiration
    public function sessionExpire($intime) {
        $f3 = Base::instance();
        $t = time();
        $t0 = $_SESSION[$intime];
        $diff = $t - $t0;
        if ($diff > $f3->get('sessionTimeOut') || !isset($t0))
        {
            return true;
        }
        else
        {
            $_SESSION[$intime] = time();
        }
    }

    function logout()
    {
        $this->f3->clear('SESSION');
        $this->f3->reroute('/');
    }

    //generate and send email
    public function sendSmtpEmail($type, $surname, $name, $email, $referral = null, $password = null, $rank = null) {
        $mail = new PHPMailer;

        if ($type == "coach_register") {
            $message = file_get_contents('css/email_templates/coach_register.html');
            $message = str_replace('%NAME%', $name, $message);
            $message = str_replace('%SURNAME%', $surname, $message);
            $message = str_replace('%PASSWORD%', $password, $message);
            $message = str_replace('%URL_SITE%', 'http://www.hoopdrillz.com/', $message);
            $message = str_replace('%URL_COACH%', 'http://www.hoopdrillz.com/coach', $message);
            $message = str_replace('%URL_REFERRAL%', 'http://prelaunch.hoopdrillz.com/'.$referral, $message);
            $mail->Subject = $this->f3->get('eml_template_coach_reg_subject');
            /*
            $message = $this->f3->get('eml_template_coach_reg_line1');
            $message .= $this->f3->get('eml_template_coach_reg_line2').$name.'<br>';
            $message .= $this->f3->get('eml_template_coach_reg_line3').$surname.'<br>';
            $message .= $this->f3->get('eml_template_coach_reg_line4').$coach_referral.'<br>';
            $message .= $this->f3->get('eml_template_coach_reg_line5').$password.'<br><br>';
            $message .= $this->f3->get('eml_template_coach_reg_line6').'<br>';
            */
        } elseif ($type == "coach_recover") {
            $mail->Subject = $this->f3->get('eml_template_coach_recover_subject');
            $message = $this->f3->get('eml_template_coach_rec_line1').$surname.' '.$name.',<br><br>';;
            $message .= $this->f3->get('eml_template_coach_rec_line2');
            $message .= $this->f3->get('eml_template_coach_rec_line3').$password.'<br><br>';
            $message .= $this->f3->get('eml_template_coach_rec_line4');
            $message .= $this->f3->get('eml_template_coach_rec_line5');
        } elseif ($type == "admin_recover") {
            $mail->Subject = $this->f3->get('eml_template_admin_recover_subject');
            $message = $this->f3->get('eml_template_admin_rec_line1').$surname.' '.$name.',<br><br>';;
            $message .= $this->f3->get('eml_template_admin_rec_line2');
            $message .= $this->f3->get('eml_template_admin_rec_line3').$password.'<br><br>';
            $message .= $this->f3->get('eml_template_admin_rec_line4');
            $message .= $this->f3->get('eml_template_admin_rec_line5');
        } elseif ($type == "user_register") {
            $mail->Subject = $this->f3->get('eml_template_user_register_subject');
            $message = file_get_contents('css/email_templates/user_register.html');
            $message = str_replace('%WAIT_RANK%', $rank, $message);
            $message = str_replace('%URL_REFERRAL%', 'http://prelaunch.hoopdrillz.com/'.$referral, $message);
        }


        $mail->SMTPDebug = $this->f3->get('smtpdebug');
        $mail->isSMTP();
        $mail->Debugoutput = 'html';
        $mail->Host = $this->f3->get('smtpsrv');
        $mail->SMTPAuth = true;
        $mail->Username = $this->f3->get('smtpuser');
        $mail->Password = $this->f3->get('smtppass');
        $mail->SMTPSecure = $this->f3->get('smtpsecure');
        $mail->Port = $this->f3->get('smtpport');
        $mail->setFrom($this->f3->get('smtpfrom'), $this->f3->get('smtpfromname'));
        $mail->addAddress($email, $surname.', '.$name);
        $mail->isHTML(true);

        $mail->Body    = $message;
        $mail->AltBody = $message;

        if(!$mail->send()) {
            return false;
        } else {
            return true;
        }

    }

    //generate error message
    public function setErrorStatus($error_msg,$route) {
        $this->f3->set('SESSION.haserror', '1');
        $this->f3->set('SESSION.error',$this->f3->get($error_msg));
        $this->f3->reroute($route);
    }

    public function checkAccess($pageType,$clearaccess,$reroute) {
        $email = $this->f3->get('SESSION.email');
        $cid = $this->f3->get('SESSION.cid');
        $lvl = $this->f3->get('SESSION.lvl');

        if ($clearaccess != true) {
            if(!$email || !$cid || !$lvl) {
                return $this->f3->reroute('/');
            }

            if ($pageType != $lvl) {
                return $this->f3->reroute('/');
            }

            if ($this->sessionExpire('intime')) {
                $this->logout();
            }
        }

        if ($reroute === true) {
            if ($email && $cid) {
                switch ($pageType) {
                    case 'admin':
                        return $this->f3->reroute($this->f3->get('admin_default_loggedin').$cid);
                        break;
                    case 'user':
                        return $this->f3->reroute($this->f3->get('user_default_loggedin_route').$cid);
                        break;
                    case 'coach':
                        return $this->f3->reroute($this->f3->get('coach_login_already_logged_route').$cid);
                        break;
                    default:
                        $this->logout();
                }
            }
        }
    }
}