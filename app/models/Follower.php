<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/4/17
 * Time: 10:38 PM
 */
class Follower extends DB\SQL\Mapper
{

    public function __construct(DB\SQL $db)
    {
        parent::__construct($db, 'users_followers');
    }

    public function registerFollower($email, $user_id, $coach_id)
    {
        $this->load(array('email=?', $email));
        if ($user_id != null) {
            $userid = $user_id;
        } else {
            $userid = $this->user_id;
        }

        if ($coach_id != null) {
            $coachid = $coach_id;
        } else {
            $coachid = $this->coach_id;
        }

        $this->email = $email;
        $this->user_id = $userid;
        $this->coach_id = $coachid;
        $this->save();
        return $lastInsertedID = $this->get('id');
    }

    public function getCountAllFollowers()
    {
        $mcount = $this->db->exec('SELECT COUNT(*) as mcount FROM users_followers');
        return $mcount;
    }

    public function getIfFollowing($email)
    {
        $this->load(array('email=?', $email));
        return $this->loaded();
    }

    public function getFollowerByCoachId($cid)
    {
        $this->load(array('coach_id=?', $cid));
        return $this->loaded();
    }

    public function getMonthlyUsersByCoachId($cid, $year, $month)
    {
        $mcount = $this->db->exec('SELECT COUNT(*) as mcount FROM users_followers WHERE YEAR(coach_follow_date) = :year AND MONTH(coach_follow_date) = :month AND coach_id = :cid', array(':year' => $year, ':month' => $month, ':cid' => $cid));
        return $mcount;
    }
}