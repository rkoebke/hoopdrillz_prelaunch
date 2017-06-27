<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/4/17
 * Time: 7:37 PM
 */
class User extends DB\SQL\Mapper
{

    public function __construct(DB\SQL $db)
    {
        parent::__construct($db, 'users');
    }

    public function generateHash($password)
    {
        $cost = 10;
        $salt = strtr(base64_encode(random_bytes(16)), '+', '.');
        $salt = sprintf("$2a$%02d$", $cost) . $salt;
        $hash = crypt($password, $salt);
        return $hash;
    }

    /*
    public function registerUser($name, $surname, $email, $hash, $coach_id, $user_referral)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->password = $hash;
        $this->coach_id = $coach_id;
        $this->user_referral_id = $user_referral;
        $this->save();
    }
    */

    public function registerUser($email, $coach_id) {
        $user_referral = "0x001".bin2hex(random_bytes(16));
        if ($coach_id != null) {
            $coachid = $coach_id;
        } else {
            $coachid = 0;
        }
        $this->email = $email;
        $this->coach_id = $coachid;
        $this->referral = $user_referral;
        $this->save();
    }

    public function getByEmail($email)
    {
        $this->load(array('email=?', $email));
    }

    public function getByReferral($referral)
    {
        $this->load(array('referral=?', $referral));
        return $this->loaded();
    }

    public function getIdFromReferral($referral) {
        $this->load(array('referral=?', $referral));
        return $this->id;
    }

    public function getUserReferralById($userid) {
        $this->load(array('id=?', $userid));
        return $this->referral;
    }

    public function getUserReferralByEmail($email) {
        $this->load(array('email', $email));
        return $this->referral;
    }

    public function getCountAllUsers()
    {
        $mcount = $this->db->exec('SELECT COUNT(*) as mcount FROM users');
        return $mcount;
    }

    public function getUserFollowers($cid)
    {
        $mcount = $this->db->exec('SELECT COUNT(*) as mcount FROM users_followers WHERE user_id = :cid', array(':cid' => $cid));
        return $mcount;
    }

    public function getUserWaitPosition($userid)
    {
        $position = $this->db->exec(
            array(
                'SET @prev_value = NULL;',
                'SET @rank_count = 0;',
                'SELECT user_id, rank FROM ( SELECT id, user_id, CASE WHEN @prev_value = user_id THEN @rank_count WHEN @prev_value := user_id THEN @rank_count := @rank_count + 1 END AS rank FROM users_followers ORDER BY user_id) AS subquery WHERE user_id = :userid LIMIT 1;'
            ),
            array(
                array(),
                array(),
                array(':userid' => $userid),
            ));

        //if we have some rank for user display it
        //else put him on the last position in list based on registered users count
        if (is_numeric($position[0]["rank"]) === true) {
            return $position[0]["rank"];
        } else {
            $lastuid = $this->getCountAllUsers();
            return $lastuid[0]["mcount"];
        }

    }
}