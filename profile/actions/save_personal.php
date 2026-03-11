<?php
require_once($sr_root . "/db/db.php");
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Gather and validate input data
$lastname = trim($_POST['lastname']) ? trim($_POST['lastname']) : null;
$midname = trim($_POST['midname']);
$firstname = trim($_POST['firstname']);
$maidenname = trim($_POST['maidenname']);
$suffix = trim($_POST['suffix']);
$person_num = trim($_POST['person_num']);
$company_num = trim($_POST['company_num']);
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$telephone = trim($_POST['telephone']);
$sss = trim($_POST['sss']);
$pagibig = trim($_POST['pagibig']);
$philhealth = trim($_POST['philhealth']);
$tin = trim($_POST['tin']);
$Pprovince = trim($_POST['Pprovince']);
$Pmunicipal = trim($_POST['Pmunicipal']);
$Pbrngy = trim($_POST['Pbrngy']);
$Cprovince = trim($_POST['Cprovince']);
$Cmunicipal = trim($_POST['Cmunicipal']);
$Cbrngy = trim($_POST['Cbrngy']);
$Bprovince = trim($_POST['Bprovince']);
$Bmunicipal = trim($_POST['Bmunicipal']);
$Bbrngy = trim($_POST['Bbrngy']);
$birthdate = $_POST['birthdate'];
$civilstat = trim($_POST['civilstat']);
$sex = isset($_POST['sex']) ? $_POST['sex'] : null;
$religion = trim($_POST['religion']);
$height = trim($_POST['height']);
$weight = trim($_POST['weight']);
$bloodtype = trim($_POST['bloodtype']);
$dialect = trim($_POST['dialect']);
$permaddlocInput = trim($_POST['permaddlocInput']);
$curaddlocInput = trim($_POST['curaddlocInput']);
$birthaddlocInput = trim($_POST['birthaddlocInput']);

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    // Begin transaction
    $port_db->beginTransaction();

    // Check if entry exists in tbl201_persinfo
    $stmt = $port_db->prepare("SELECT pers_id FROM tbl201_persinfo WHERE pers_empno = :pers_empno");
    $stmt->execute(['pers_empno' => $user_id]);
    $pers_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pers_info) {
        // Update existing entry in tbl201_persinfo
        $stmt = $port_db->prepare("UPDATE tbl201_persinfo SET 
            pers_lastname = :pers_lastname, 
            pers_midname = :pers_midname, 
            pers_firstname = :pers_firstname, 
            pers_maidenname = :pers_maidenname, 
            pers_prefname = :pers_prefname, 
            pers_civilstat = :pers_civilstat, 
            pers_sex = :pers_sex, 
            pers_religion = :pers_religion, 
            pers_birthdate = :pers_birthdate, 
            pers_bloodtype = :pers_bloodtype, 
            pers_dialect = :pers_dialect, 
            pers_height = :pers_height, 
            pers_weight = :pers_weight 
            WHERE pers_id = :pers_id");

        $stmt->execute([
            'pers_lastname' => $lastname, 
            'pers_midname' => $midname, 
            'pers_firstname' => $firstname, 
            'pers_maidenname' => $maidenname,
            'pers_prefname' => $suffix,
            'pers_civilstat' => $civilstat, 
            'pers_sex' => $sex, 
            'pers_religion' => $religion, 
            'pers_birthdate' => $birthdate, 
            'pers_bloodtype' => $bloodtype, 
            'pers_dialect' => $dialect, 
            'pers_height' => $height, 
            'pers_weight' => $weight,
            'pers_id' => $pers_info['pers_id']
        ]);
    } else {
        // Insert new entry in tbl201_persinfo
        $stmt = $port_db->prepare("INSERT INTO tbl201_persinfo (
            pers_empno, 
            pers_lastname, 
            pers_midname, 
            pers_firstname, 
            pers_maidenname, 
            pers_prefname,
            pers_civilstat, 
            pers_sex, 
            pers_religion, 
            pers_birthdate, 
            pers_bloodtype, 
            pers_dialect, 
            pers_height, 
            pers_weight) 
            VALUES (
            :pers_empno, 
            :pers_lastname, 
            :pers_midname, 
            :pers_firstname, 
            :pers_maidenname, 
            :pers_prefname,
            :pers_civilstat, 
            :pers_sex, 
            :pers_religion, 
            :pers_birthdate, 
            :pers_bloodtype, 
            :pers_dialect, 
            :pers_height, 
            :pers_weight)");

        $stmt->execute([
            'pers_empno' => $user_id, 
            'pers_lastname' => $lastname, 
            'pers_midname' => $midname, 
            'pers_firstname' => $firstname, 
            'pers_maidenname' => $maidenname,
            'pers_prefname' => $suffix,
            'pers_civilstat' => $civilstat, 
            'pers_sex' => $sex, 
            'pers_religion' => $religion, 
            'pers_birthdate' => $birthdate, 
            'pers_bloodtype' => $bloodtype, 
            'pers_dialect' => $dialect, 
            'pers_height' => $height, 
            'pers_weight' => $weight
        ]);
    }

    // Check if entry exists in tbl201_address
    $stmt = $port_db->prepare("SELECT add_id FROM tbl201_address WHERE add_empno = :add_empno");
    $stmt->execute(['add_empno' => $user_id]);
    $address_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($address_info) {
        // Update existing entry in tbl201_address
        $stmt = $port_db->prepare("UPDATE tbl201_address SET 
            add_perm_prov = :add_perm_prov, 
            add_perm_city = :add_perm_city, 
            add_perm_brngy = :add_perm_brngy, 
            add_cur_prov = :add_cur_prov, 
            add_cur_city = :add_cur_city, 
            add_cur_brngy = :add_cur_brngy, 
            add_birth_prov = :add_birth_prov, 
            add_birth_city = :add_birth_city, 
            add_birth_brngy = :add_birth_brngy,  
            add_perm_location = :add_perm_location,
            add_cur_location = :add_cur_location, 
            add_birth_location = :add_birth_location, 
            add_status = :add_status 
            WHERE add_id = :add_id");

        $stmt->execute([
            'add_perm_prov' => $Pprovince,
            'add_perm_city' => $Pmunicipal,
            'add_perm_brngy' => $Pbrngy,
            'add_cur_prov' => $Cprovince,
            'add_cur_city' => $Cmunicipal,
            'add_cur_brngy' => $Cbrngy,
            'add_birth_prov' => $Bprovince,
            'add_birth_city' => $Bmunicipal,
            'add_birth_brngy' => $Bbrngy,
            'add_perm_location' => $permaddlocInput,
            'add_cur_location' => $curaddlocInput,
            'add_birth_location' => $birthaddlocInput,
            'add_status' => '1',
            'add_id' => $address_info['add_id']
        ]);
    } else {
        // Insert new entry in tbl201_address
        $stmt = $port_db->prepare("INSERT INTO tbl201_address (
            add_empno, 
            add_perm_prov, 
            add_perm_city, 
            add_perm_brngy, 
            add_cur_prov, 
            add_cur_city, 
            add_cur_brngy, 
            add_birth_prov, 
            add_birth_city, 
            add_birth_brngy, 
            add_perm_location,
            add_cur_location,
            add_birth_location,
            add_status) VALUES (
            :add_empno, 
            :add_perm_prov, 
            :add_perm_city, 
            :add_perm_brngy, 
            :add_cur_prov, 
            :add_cur_city, 
            :add_cur_brngy, 
            :add_birth_prov, 
            :add_birth_city, 
            :add_birth_brngy,
            :add_perm_location, 
            :add_cur_location, 
            :add_birth_location,  
            :add_status)");

        $stmt->execute([
            'add_empno' => $user_id,
            'add_perm_prov' => $Pprovince,
            'add_perm_city' => $Pmunicipal,
            'add_perm_brngy' => $Pbrngy,
            'add_cur_prov' => $Cprovince,
            'add_cur_city' => $Cmunicipal,
            'add_cur_brngy' => $Cbrngy,
            'add_birth_prov' => $Bprovince,
            'add_birth_city' => $Bmunicipal,
            'add_birth_brngy' => $Bbrngy,
            'add_perm_location' => $permaddlocInput,
            'add_cur_location' => $curaddlocInput,
            'add_birth_location' => $birthaddlocInput,
            'add_status' => '1'
        ]);
    }

    // Check if entry exists in tbl201_contact
    $stmt = $port_db->prepare("SELECT cont_id FROM tbl201_contact WHERE cont_empno = :cont_empno");
    $stmt->execute(['cont_empno' => $user_id]);
    $contact_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contact_info) {
        // Update existing entry in tbl201_contact
        $stmt = $port_db->prepare("UPDATE tbl201_contact SET 
            cont_person_num = :cont_person_num, 
            cont_company_num = :cont_company_num, 
            cont_telephone = :cont_telephone, 
            cont_email = :cont_email,
            cont_status = :cont_status
            WHERE cont_id = :cont_id");

        $stmt->execute([
            'cont_person_num' => $person_num,
            'cont_company_num' => $company_num,
            'cont_telephone' => $telephone,
            'cont_email' => $email,
            'cont_status' => '1',
            'cont_id' => $contact_info['cont_id']
        ]);
    } else {
        // Insert new entry in tbl201_contact
        $stmt = $port_db->prepare("INSERT INTO tbl201_contact (
            cont_empno, 
            cont_person_num, 
            cont_company_num, 
            cont_telephone, 
            cont_email, 
            cont_status) VALUES (
            :cont_empno, 
            :cont_person_num, 
            :cont_company_num, 
            :cont_telephone, 
            :cont_email, 
            :cont_status)");

        $stmt->execute([
            'cont_empno' => $user_id,
            'cont_person_num' => $person_num,
            'cont_company_num' => $company_num,
            'cont_telephone' => $telephone,
            'cont_email' => $email,
            'cont_status' => '1'
        ]);
    }

    // Check if entry exists in tbl201_gov_req
    $stmt = $port_db->prepare("SELECT gov_id FROM tbl201_gov_req WHERE gov_empno = :gov_empno");
    $stmt->execute(['gov_empno' => $user_id]);
    $gov_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gov_info) {
        // Update existing entry in tbl201_gov_req
        $stmt = $port_db->prepare("UPDATE tbl201_gov_req SET 
            gov_sss = :gov_sss, 
            gov_pagibig = :gov_pagibig, 
            gov_philhealth = :gov_philhealth, 
            gov_tin = :gov_tin,
            gov_status = :gov_status
            WHERE gov_id = :gov_id");

        $stmt->execute([
            'gov_sss' => $sss,
            'gov_pagibig' => $pagibig,
            'gov_philhealth' => $philhealth,
            'gov_tin' => $tin,
            'gov_status' => '1',
            'gov_id' => $gov_info['gov_id']
        ]);
    } else {
        // Insert new entry in tbl201_gov_req
        $stmt = $port_db->prepare("INSERT INTO tbl201_gov_req (
            gov_empno, 
            gov_sss, 
            gov_pagibig, 
            gov_philhealth, 
            gov_tin, 
            gov_status) VALUES (
            :gov_empno, 
            :gov_sss, 
            :gov_pagibig, 
            :gov_philhealth, 
            :gov_tin, 
            :gov_status)");

        $stmt->execute([
            'gov_empno' => $user_id,
            'gov_sss' => $sss,
            'gov_pagibig' => $pagibig,
            'gov_philhealth' => $philhealth,
            'gov_tin' => $tin,
            'gov_status' => '1'
        ]);
    }

    // Commit transaction
    $port_db->commit();

    echo json_encode(['success' => true, 'message' => 'Personal info saved successfully']);
} catch (Exception $e) {
    // Roll back transaction if there was an error
    if ($port_db->inTransaction()) {
        $port_db->rollBack();
    }
    
    error_log("Error saving personal info: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to save personal info', 'details' => $e->getMessage()]);
}