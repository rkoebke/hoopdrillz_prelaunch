<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/4/17
 * Time: 3:40 PM
 */
class Coach extends DB\SQL\Mapper
{

    public function __construct(DB\SQL $db)
    {
        parent::__construct($db, 'coaches');
    }

    public function generateRandom() {
        return substr(hash('sha512',rand()),0,12);
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
    public function registerCoach($name, $surname, $email, $hash, $referralid)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->password = $hash;
        $this->referral_id = $referralid;
        $this->save();
    }
    */

    public function registerCoach($name, $surname, $email, $password) {
        $coach_referral = "0x002".bin2hex(random_bytes(16));
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->referral_id = $coach_referral;

        $controller = new Controller();
        /* send email to coach */
        $sendMail = $controller->sendSmtpEmail('coach_register',$surname,$name,$email,$coach_referral,$password,null);
        if($sendMail === true) {
            $this->password = $this->generateHash($password);
            $this->save();
            return $this->get('id');
        }
    }

    function updateCoach($id,$name,$surname,$email,$password)
    {
        $this->load(array('id=?',$id));

        if ($name != $this->name) {
            $coach_name = $name;
        } else {
            $coach_name = $this->name;
        }

        if ($surname != $this->surname) {
            $coach_surname = $surname;
        } else {
            $coach_surname = $this->surname;
        }

        if ($email != $this->email) {
            $coach_email = $email;
        } else {
            $coach_email = $this->email;
        }

        if(!empty($name)) { $this->name = $coach_name; }
        if(!empty($surname)) { $this->surname = $coach_surname; }
        if(!empty($email)) { $this->email = $coach_email; }
        if(!empty($password)) {
            $controller = new Controller;
            $sendMail = $controller->sendSmtpEmail('coach_recover',$this->surname,$this->name,$this->email,null,$password,null);
            if($sendMail === true) {
                $this->password = $this->generateHash($password);
                $this->save();
                return $this->get('id');
            }
        } else {
            $this->save();
            return $this->get('id');
        }
    }

    public function getIfCoach($cid, $email)
    {
        $this->load(array('id=? AND email=?', $cid, $email));
    }

    public function getById($cid) {
        $this->load(array('id=?', $cid));
        return $this->query[0];
    }

    public function getCountEnabledCoaches()
    {
        $mcount = $this->db->exec('SELECT COUNT(*) as mcount FROM coaches WHERE enabled = :enabled', array(':enabled' => 'Y'));
        return $mcount;
    }

    public function getCountDisabledCoaches()
    {
        $mcount = $this->db->exec('SELECT COUNT(*) as mcount FROM coaches WHERE enabled = :enabled', array(':enabled' => 'N'));
        return $mcount;
    }

    public function getIfEnabled($email)
    {
        $this->load(array('email=? AND enabled=?', $email, 'Y'));
    }

    public function getByEmail($email)
    {
        $this->load(array('email=?', $email));
    }

    public function getIfEmailIsUsed($email) {
        $this->load(array('email=?', $email));
        return $this->loaded();
    }

    public function getByReferral($referral)
    {
        $this->load(array('referral_id=?', $referral));
        return $this->loaded();
    }

    public function getIdFromReferral($referral) {
        $this->load(array('referral_id=?', $referral));
        return $this->id;
    }

    public function getAllCoaches()
    {
        $this->load();
        return $this->query;
    }

}