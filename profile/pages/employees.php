<div class="page-wrapper">
    <div class="page-body">
        <div class="row">
            <?php if (!empty($profsidenav)) include_once($profsidenav); ?>
            <div class="col-md-1">
             
            </div>
            <div class="col-md-8" id="prof-center">
                <div class="card">
                    <div class="card-block" id="prof-card">
                      <div id="personal-info">
                        <?php
                        $pdo = new PDO("mysql:host=localhost;dbname=portal_db", "root", "");
                        
                        if (isset($_GET['search'])) {
                            $searchTerm = $_GET['search'];
                            $stmt = $pdo->prepare("SELECT 
                                a.bi_empno, 
                                CONCAT(a.bi_empfname, ' ', a.bi_empmname, ' ', a.bi_emplname) AS fullname, 
                                b.`jrec_outlet`,
                                b.`jrec_department`,
                                jd.`jd_title`,
                                jd.`jd_company` as company,
                                cont.`cont_person_num`,
                                cont.`cont_company_num`,
                                cont.`cont_email`,
                                cont.`cont_telephone`,
                                dept.`Dept_Name`
                            FROM 
                                tbl201_basicinfo a
                            LEFT JOIN 
                                tbl201_jobrec b ON a.bi_empno = b.jrec_empno
                            LEFT JOIN 
                                tbl201_basicinfo head ON b.jrec_reportto = head.bi_empno
                            LEFT JOIN 
                                tbl_jobdescription jd ON jd.jd_code = b.jrec_position
                            LEFT JOIN 
                                tbl201_jobinfo ji ON ji.ji_empno = a.bi_empno
                            LEFT JOIN 
                                tbl201_contact cont ON cont.`cont_empno` = a.`bi_empno`
                            LEFT JOIN 
                                tbl_department dept ON dept.`Dept_Code` = b.`jrec_department`
                            WHERE 
                                CONCAT(a.bi_empfname,' ', a.bi_emplname, ' | ',dept.Dept_Name) = ?
                                AND a.datastat = 'current'
                                AND b.jrec_type = 'Primary'
                                AND b.jrec_status = 'Primary'
                                AND ji.ji_remarks = 'Active'");
                            $stmt->execute([$searchTerm]);
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($result) {
                                echo '<div class="profile">
                                        <img src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($result["bi_empno"]) . '.JPG" alt="User-Profile-Image" width="100" height="100" style="border-radius: 50px;">
                                        <div class="basic-info">
                                          <span id="userName">
                                          ' . htmlspecialchars($result["fullname"]) . '
                                          </span>
                                          <p>' . htmlspecialchars($result["jd_title"]) .' | '. htmlspecialchars($result["jrec_department"]) .'</p>
                                          <div style="display:flex;justify-content: space-between;flex-wrap: wrap;gap: 10px;">
                                          <p style="margin-right:20px;font-size: 13px !important;"><i class="icofont icofont-smart-phone"></i>: ' . htmlspecialchars($result["cont_person_num"]) .'</p>
                                          <p style="margin-right:20px;font-size: 13px !important;"><i class="icofont icofont-ui-cell-phone"></i>: '. htmlspecialchars($result["cont_company_num"]) . '</p>
                                          <p style="margin-right:20px;font-size: 13px !important;"><i class="icofont icofont-email"></i>: ' . htmlspecialchars($result["cont_email"]) .'</p>
                                          <p><i class="icofont icofont-phone-circle"></i>: '. htmlspecialchars($result["cont_telephone"]) . '</p>
                                          </div>
                                        </div>
                                      </div>';
                            } else {
                                echo "<h1>No results found</h1>";
                            }
                        }
                        ?>

                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>