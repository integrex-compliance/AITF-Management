<?php

//session_start();
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
date_default_timezone_set('Asia/Kolkata');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Utitlity {

    static function get_company_list() {
        global $db;
        $selectCompany = "SELECT id, companyName FROM `company` WHERE STATUS = 1 ORDER BY `id` ASC";
        $companies = $db->query($selectCompany)->fetchAll(PDO::FETCH_ASSOC);
        return $companies;
    }

    static function checkUserPermission($feature) {
        if (!isset($_SESSION['id'])) return 0;
        
        // Top Mgmt has full software access; all other roles use feature grants.
        if (self::checkForDirector() || self::checkForUserMainAdmin()) {
            return 1; 
        }

        global $db;
        if(!$db) return 0;
        try {
            $stmt = $db->prepare("SELECT is_allowed FROM user_permissions WHERE user_id = :uid AND feature = :feat");
            $stmt->execute(['uid' => $_SESSION['id'], 'feat' => $feature]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res && $res['is_allowed'] == 1) {
                return 1;
            }
        } catch(Exception $e) {}
        
        return 0;
    }

    static function checkIfLoggedIn() {
        if (isset($_SESSION ['username'])) {
            return 1;
        } else {
            return 0;
        }
    }

    static function checkForDirector() {
        if (isset($_SESSION ['username']) && !empty($_SESSION ['username']) && aitf_is_top_management_role())
            return 1;
        else
            return 0;
    }
    
    static function checkForUserMainAdmin() {
        if (isset($_SESSION ['username']) && !empty($_SESSION ['username']) && aitf_is_top_management_role())
            return 1;
        else
            return 0;
    }
    
    static function checkForUserAdmin() {
        if (isset($_SESSION ['username']) && !empty($_SESSION ['username']) && in_array($_SESSION ['role'] ?? '', array('Admin', 'Top Mgmt'), true))
            return 1;
        else
            return 0;
    }    

    static function checkForUserMarketing() {
        if (isset($_SESSION ['username']) && !empty($_SESSION ['username']) && $_SESSION ['role'] == "Marketing")
            return 1;
        else
            return 0;
    }

    static function checkForUserConsultant() {
        if (isset($_SESSION ['username']) && !empty($_SESSION ['username']) && $_SESSION ['role'] == "Application Reviewer")
            return 1;
        else
            return 0;
    }

    static function checkForUserFinance() {
        if (isset($_SESSION ['username']) && !empty($_SESSION ['username']) && $_SESSION ['role'] == "Finance")
            return 1;
        else
            return 0;
    }

    static function checkForOfficeStaff() {
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) && $_SESSION['role'] == "Office Staff")
            return 1;
        else
            return 0;
    }
    
    static function checkForMainAdmin(){
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) && aitf_is_top_management_role())
            return 1;
        else
            return 0;
    }
    
    static function checkForStaff(){
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) && in_array($_SESSION['role'] ?? '', array("Office Staff", "Auditors", "Auditor", "Application Reviewer", "Top Mgmt"), true))
            return 1;
        else
            return 0;
    }
    
    static function checkClient(){
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) && $_SESSION['role'] == "client")
            return 1;
        else
            return 0;
    }
    
    static function checkExternalUser(){
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) && $_SESSION['role'] == "External")
            return 1;
        else
            return 0;
    }

    static function checkForAuditor(){
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) && aitf_is_auditor_role())
            return 1;
        else
            return 0;
    }

    /**
     * Check if the current user is accessing the system with limited/permission-only access.
     * This is true for Auditors (or similar restricted roles) who have been granted specific 
     * feature permissions by the Director but should NOT see the full sidebar menu.
     * Returns true if the user's ONLY access to the current page is via a permission grant.
     */
    static function isLimitedAccessUser() {
        if (!isset($_SESSION['role'])) return false;
        $role = $_SESSION['role'];
        // These roles have inherent full/partial menu access — NOT limited
        $fullAccessRoles = array('Top Mgmt', 'Admin', 'Office Staff', 'Finance', 'Marketing', 'Application Reviewer', 'Report Reviewer', 'Auditor');
        if (in_array($role, $fullAccessRoles, true)) {
            return false;
        }
        // Auditor, STO, and other roles accessing via Director-granted permissions are limited
        return true;
    }

    static function compress($source, $destination, $quality) {
        $info = getimagesize($source);
        if ($info ['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);
        elseif ($info ['mime'] == 'image/gif')
            $image = imagecreatefromgif($source);
        elseif ($info ['mime'] == 'image/png')
            $image = imagecreatefrompng($source);
        imagejpeg($image, $destination, $quality);
        return $destination;
    }

    static function getHomePageForUser() {
        if (Utitlity::checkForAuditor()) {
            return "auditorDashboard.php";
        }
        if (Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin()) {
            return "homePage.php";
        }
        if (Utitlity::checkForUserConsultant() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin()) {
            return "workingDetails.php";
        }
        if (Utitlity::checkForUserMarketing() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin()) {
            return "marketingDetails.php";
        }
        if (Utitlity::checkForUserFinance() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin()) {
            return "financeDetails.php";
        }
    }

}
