<?php
/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/4/17
 * Time: 10:36 PM
 */

class FollowerController extends Controller {

    function register() {
        $email = $this->f3->get('POST.email');
        $referral = $this->f3->get('POST.referral');

        $audit = \Audit::instance();
        if ($audit->email($email, TRUE) === FALSE) {
            $this->setErrorStatus('follow_register_invalid_mail', $this->f3->get('follow_default_route'));
        }

        //check if all fields are completed
        if (!$email || !$referral) {
            $this->setErrorStatus('follow_register_missing_fields', $this->f3->get('follow_default_route'));
        }

        $user = new User($this->db);
        $follower = new Follower($this->db);

        $follower->getIfFollowing($email);
        if (!$follower->dry()) {
            $this->setErrorStatus('follow_register_already_follow', $this->f3->get('follow_default_route'));
        }

        //check if referral is valid
        $user->getByReferral($referral);
        if ($user->dry()) {
            $this->setErrorStatus('follow_register_unknown_referral', $this->f3->get('follow_default_route'));
        }

        $follower->registerFollower($email, $user->id);
        $this->f3->reroute('{{@BASE}}/');
    }

}