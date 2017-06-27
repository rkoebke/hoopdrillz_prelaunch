<?php

/**
 * Created by Nu Am Chef Azi Project for HoopDrillz.
 * User: crc
 * Date: 5/4/17
 * Time: 3:40 PM
 */
class Admin extends DB\SQL\Mapper
{

    public function __construct(DB\SQL $db)
    {
        parent::__construct($db, 'admins');
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

    public function registerAdmin($name, $surname, $email, $hash)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->password = $hash;
        $this->save();
    }

    public function getByEmail($email)
    {
        $this->load(array('email=?', $email));
        return $this;
    }

    public function getById($id)
    {
        $this->load(array('id=?', $id));
        return $this;
    }

    public function getAdminDetails()
    {
        $this->getByEmail(base::instance()->get('SESSION.email'));
        return $this;
    }

    public function getCountAllAdmins()
    {
        $mcount = $this->db->exec('SELECT COUNT(*) as mcount FROM admins');
        return $mcount;
    }


    public function coachEnable($cid)
    {
        $coach = new Coach($this->db);
        $coach->load(array('id=?', $cid));
        $coach->enabled='Y';
        try {
            $coach->save();
            return "ok";
        } catch (\Exception $e) {
            return "nok";
        }
    }

    public function coachDisable($cid)
    {
        $coach = new Coach($this->db);
        $coach->load(array('id=?', $cid));
        $coach->enabled='N';
        try {
            $coach->save();
            return "ok";
        } catch (\Exception $e) {
            return "nok";
        }
    }

    public function getAllAdmins()
    {
        $this->load();
        return $this->query;
    }

    public function adminEnable($cid)
    {
        $this->load(array('id=?', $cid));
        $this->enabled='Y';
        try {
            $this->save();
            return "ok";
        } catch (\Exception $e) {
            return "nok";
        }
    }

    public function adminDisable($cid)
    {
        $this->load(array('id=?', $cid));
        $this->enabled='N';
        try {
            $this->save();
            return "ok";
        } catch (\Exception $e) {
            return "nok";
        }
    }

    function updateAdmin($id,$name,$surname,$email,$password)
    {
        $this->load(array('id=?',$id));

        if ($name != $this->name) {
            $admin_name = $name;
        } else {
            $admin_name = $this->name;
        }

        if ($surname != $this->surname) {
            $admin_surname = $surname;
        } else {
            $admin_surname = $this->surname;
        }

        if ($email != $this->email) {
            $admin_email = $email;
        } else {
            $admin_email = $this->email;
        }

        if(!empty($name)) { $this->name = $admin_name; }
        if(!empty($surname)) { $this->surname = $admin_surname; }
        if(!empty($email)) { $this->email = $admin_email; }
        if(!empty($password)) {
            $controller = new Controller;
            $sendMail = $controller->sendSmtpEmail('admin_recover',$this->surname,$this->name,$this->email,null,$password,null);
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

    function getTotalCoachEmails()
    {
        $mcount = $this->db->exec('SELECT COUNT("email") as mcount FROM coaches');
        return $mcount;
    }

    function getTotalUserEmails()
    {
        $mcount = $this->db->exec('SELECT COUNT("email") as mcount FROM users');
        return $mcount;
    }

    function getTotalFollowerEmails()
    {
        $mcount = $this->db->exec('SELECT COUNT("email") as mcount FROM users_followers');
        return $mcount;
    }

    function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",") {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'";');

        $f = fopen('php://output', 'w');

        foreach ($array as $line) {
            fputcsv($f, $line, $delimiter);
        }
    }

    function getExportEmail($type)
    {
        if ($type == "coach") {
            $emails = $this->db->exec('SELECT email FROM coaches');
        } elseif ($type == "user") {
            $emails = $this->db->exec('SELECT email FROM users');
        } elseif ($type == "follower") {
            $emails = $this->db->exec('SELECT email FROM users_followers');
        }
        return $emails;
    }
}