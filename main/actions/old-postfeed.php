<?php
require_once($main_root . "/db/db.php");
require_once($main_root . "/actions/get_personal.php");

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    $date = date('Y-m-d');
    $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
    $items_per_page = 3;
    $offset = ($page - 1) * $items_per_page;

    $userIdentifier = $empno;
    $cacheFile = "cache/postfeeddata_user_{$userIdentifier}_page_{$page}.json";
    $cacheTime = 60; 

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        $data = json_decode(file_get_contents($cacheFile), true);
    } else {
        $currentYear = date('Y');

        // Query for port_db
        $stmt_port = $port_db->prepare("
            SELECT a.*,b.bi_empno,b.bi_emplname,b.bi_empfname, a.ann_id as port_id
            FROM tbl_announcement a
            LEFT JOIN tbl201_basicinfo b ON a.ann_approvedby = b.bi_empno
            WHERE ann_status IN ('Approved','Reported') AND 
            (
                FIND_IN_SET(:company, a.ann_receiver) > 0 OR
                (a.ann_receiver = 'Only Me' AND a.ann_approvedby = :user) OR
                a.ann_receiver = 'All'
            )
            GROUP BY a.`ann_id`
            ORDER BY a.ann_pinned DESC, a.ann_timestatmp DESC
            LIMIT :offset, :limit
        ");
        $stmt_port->bindValue(':company', $company, PDO::PARAM_STR);
        $stmt_port->bindValue(':user', $empno, PDO::PARAM_STR);
        $stmt_port->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt_port->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt_port->execute();
        $port_data = $stmt_port->fetchAll(PDO::FETCH_ASSOC);

        // Query for hr_db
        $stmt_hr = $hr_db->prepare("
            SELECT a.*, b.bi_empno,b.bi_emplname,b.bi_empfname, a.ann_id as hris_id
            FROM tbl_announcement a
            LEFT JOIN tbl201_basicinfo b ON a.ann_approvedby = b.bi_empno
            WHERE ann_status = 'Approved' AND 
            (
                FIND_IN_SET(:company, a.ann_receiver) > 0 OR
                (a.ann_receiver = 'Only Me' AND a.ann_approvedby = :user) OR
                a.ann_receiver = 'All'
            )
            AND a.ann_type = 'LOCAL'
            AND a.`ann_end` >= :dates 
            ORDER BY a.ann_timestatmp DESC
            LIMIT :offset, :limit
        ");
        $stmt_hr->bindValue(':company', $company, PDO::PARAM_STR);
        $stmt_hr->bindValue(':user', $empno, PDO::PARAM_STR);
        $stmt_hr->bindValue(':dates', $date, PDO::PARAM_STR);
        $stmt_hr->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt_hr->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt_hr->execute();
        $hr_data = $stmt_hr->fetchAll(PDO::FETCH_ASSOC);

        // Ensure ann_pinned exists in both data sets
        foreach ($port_data as &$row) {
            $row['ann_pinned'] = isset($row['ann_pinned']) ? (int)$row['ann_pinned'] : 0;
        }
        unset($row); // break reference
        
        foreach ($hr_data as &$row) {
            $row['ann_pinned'] = 0; // HR data has no pinned field, default to 0
        }
        unset($row);

        $data = array_merge($port_data, $hr_data);

        usort($data, function($a, $b) {
            if ($a['ann_pinned'] != $b['ann_pinned']) {
                return $b['ann_pinned'] - $a['ann_pinned'];
            }
        
            $timeA = !empty($a['ann_timestatmp']) ? strtotime($a['ann_timestatmp']) : 0;
            $timeB = !empty($b['ann_timestatmp']) ? strtotime($b['ann_timestatmp']) : 0;
            return $timeB - $timeA;
        });


        // Store in cache
        file_put_contents($cacheFile, json_encode($data));
    }

    if (!empty($data)) {
        foreach ($data as $row) {
            if ($row['ann_content'] !== null) {
            $stmt = $port_db->prepare("
                SELECT COUNT(*) as reaction_count 
                FROM tbl_reaction 
                WHERE post_id = ?
                GROUP BY post_id
            ");
            $stmt->execute([$row['ann_id']]);
            $reaction = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $port_db->prepare("
                SELECT *
                FROM tbl_profile 
                WHERE prof_empno = ?
                AND prof_stat = 'active'
            ");
            $stmt->execute([$row['ann_approvedby']]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo '<section class="profile-feed" id="prof-'. htmlspecialchars($row['ann_id']) .'">';
            echo '<div class="cardbox shadow-lg bg-white">';
            echo '<div class="cardbox-heading">';
            echo '<div class="dropdown float-right">';
            // echo '<button class="btn btn-flat btn-flat-icon" type="button" data-toggle="dropdown" aria-expanded="false">';
            // echo '<em class="fa fa-ellipsis-h"></em>';
            // echo '</button>';
            if (!empty($row['ann_pinned']) && $row['ann_pinned'] !== null) {
                echo '<img src="/zen/assets/image/pin.png" width="50" height="50">';
            } else {
                echo '<button class="btn btn-flat btn-flat-icon" type="button" data-toggle="dropdown" aria-expanded="false">';
                echo '<em class="fa fa-ellipsis-h"></em>';
                echo '</button>';
            }

            echo '<div class="dropdown-menu dropdown-scale dropdown-menu-right" role="menu">';
            // echo '<a class="dropdown-item" href="#" onclick="hideProfile('. htmlspecialchars($row['ann_id']) .'); return false;"><i class="fas fa-eye-slash"></i> Hide post</a>';
            // echo '<a class="dropdown-item" href="#">Stop following</a>';
            echo '<a class="dropdown-item" href="#" data-toggle="modal" data-target="#report'. htmlspecialchars($row['ann_id']) .'" style="color:red;"><i class="fas fa-exclamation-circle"></i> Report</a>';
            echo '</div>';
            echo '</div>'; // Close dropdown
            echo '<div class="modal fade" id="report'. htmlspecialchars($row['ann_id']) .'" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <input type="hidden" name="postid" value="'. htmlspecialchars($row['ann_id']) .'">
                            <div class="modal-header">
                                <h4 class="modal-title" style="text-align: center !important;">Report '. htmlspecialchars($row['bi_empfname']) . ' '. htmlspecialchars($row['bi_emplname']).' Post</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa fa-times-circle" style="font-size:24px;"></i></span>
                                </button>
                            </div>
                            <div class="modal-body" style="padding: 10px !important;">
                                <div style="display: flex;">
                                    <label style="margin-right: 15px;">Reason:</label>
                                    <textarea name="reason" rows="5" cols="5" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default waves-effect btn-mini" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light btn-mini report-btn" data-postid="'. htmlspecialchars($row['ann_id']) .'">Report</button>
                            </div>
                        </div>
                    </div>
                </div>';
            echo '<div class="media m-0">';
            echo '<div class="d-flex mr-3">';
            if (!empty($profile['prof_image'])) {
            echo '<a href=""><img class="img-fluid rounded-circle" src="/zen/assets/profile_picture/'. htmlspecialchars($profile['prof_image']) .'" onerror="this.onerror=null; this.src="https://i.pinimg.com/736x/6e/db/e7/6edbe770213e7d6885240b2c91e9dd86.jpg";"></a>';
            }else{
            echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/'. htmlspecialchars($row['bi_empno']) .'.jpg'.'" onerror="this.onerror=null; this.src="https://i.pinimg.com/736x/6e/db/e7/6edbe770213e7d6885240b2c91e9dd86.jpg";"></a>';
            }
            echo '</div>';
            echo '<div class="media-body">';
            echo '<p class="m-0" style="font-weight: 500px;">'. htmlspecialchars($row['bi_empfname']) . ' '. htmlspecialchars($row['bi_emplname']).'</p>';
            echo '<small><span><i class="icon ion-md-pin"></i>' . date("F j, Y", strtotime($row['ann_timestatmp'])) . '</span></small>';
            echo '<small><span><i class="icon ion-md-time"></i>' . (new DateTime($row['ann_timestatmp']))->format("h:i A") . '</span></small>';
            echo '</div>'; // Close media-body
            echo '</div>'; // Close media

            echo '<div class="media m-0">';
            echo '<div class="media-body"style="margin-top:10px;">';
            echo '<span><i class="icon ion-md-time"></i>' . htmlspecialchars($row['ann_title']). '</span>';
            echo '</div>'; // Close media-body
            echo '</div>'; // Close media

            echo '</div>'; // Close cardbox-heading
            
            // Cardbox Item
            echo '<div class="cardbox-item" id="image-collage">';
            if (strpos($row['ann_content'], '<figure') !== false) {
                // Case 1: HTML with <figure> and <img>
                $imagePattern = '/<img\s+[^>]*src=["\']([^"\']+)["\']/i';
                preg_match_all($imagePattern, $row['ann_content'], $imageMatches);
                $sources = $imageMatches[1];
            
                foreach ($sources as $imgv) {
                    echo '<img class="img-fluid" style="max-height: 500px !important; cursor: pointer; margin: 5px;" 
                              src="https://teamtngc.com/hris2/pages/announcement/' . htmlspecialchars($imgv) . '" 
                              onclick="openPostOverlay(\'https://teamtngc.com/hris2/pages/announcement/' . htmlspecialchars($imgv) . '\')">';
                }
            
            } else if (strpos($row['ann_content'], '<figure onclick="openPostOverlay(\'https://teamtngc.com/hris2/pages/announcement' . $row['ann_content'] . '\')"') !== false) {
                // Case 2: Inline HTML figure tag
                echo str_replace('../announcement', 'https://teamtngc.com/hris2/pages/announcement', $row['ann_content']);
            
            } else {
                // Case 3: Comma-separated image paths
                $imagePaths = explode(',', $row['ann_content']);
            
                foreach ($imagePaths as $path) {
                    $trimmedPath = trim($path);
                    if (!empty($trimmedPath)) {
                        $fullUrl = 'https://teamtngc.com/zen/' . htmlspecialchars($trimmedPath);
                        echo '<img class="img-fluid" style="max-height: 500px !important; cursor: pointer; margin: 5px;" 
                                  src="' . $fullUrl . '" 
                                  onclick="openPostOverlay(\'' . $fullUrl . '\')">';
                    }
                }
            }

            // echo '<img class="img-fluid" style="max-height: 500px !important;cursor: pointer;" src="https://teamtngc.com/zen/' . htmlspecialchars($row['ann_content']) . '" onclick="openPostOverlay(\'https://teamtngc.com/zen/' . htmlspecialchars($row['ann_content']) . '\')">';
            // echo "".$row['ann_content']."";
            echo '</div>'; // Close cardbox-item
            
            // Cardbox Base
            echo '<div class="cardbox-base">';
            echo '<ul>';
            echo '<li>';
            echo '<div class="reaction-container">';
            $stmt = $port_db->prepare("
                SELECT reaction_type FROM tbl_reaction 
                WHERE reaction_by = ?
                AND post_id = ?
                GROUP BY reaction_type
            ");
            $stmt->execute([$user_id,$row['ann_id']]);
            $ireact = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the reaction type is 'heart'
            if ($ireact && $ireact['reaction_type'] == 'heart') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'like') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'love') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'eey') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="https://i.pinimg.com/564x/9d/04/2c/9d042cb030e250961454adf7131f76b5.jpg" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'cry') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'haha') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'wow') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'angry') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle">
                      </a>';
            } else {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <i class="ti-face-smile"></i>
                      </a>';
            }

             echo '<input type="hidden" name="post-id" value="' .htmlspecialchars($row['ann_id']). '" />
            <div class="reaction-options">
                <div name="reaction" class="reaction" data-reaction="like"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/like.gif"></div>
                <div name="reaction" class="reaction" data-reaction="eey"><img width="50" height="60" src="https://i.pinimg.com/originals/58/91/52/58915204d17860c24d4c02be7425a830.gif"></div>
                <div name="reaction" class="reaction" data-reaction="heart"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/heart.gif"></div>
                <div name="reaction" class="reaction" data-reaction="love"><img class="img" width="50" height="50" src="https://media1.tenor.com/m/63nE7vC84pIAAAAd/care-discord.gif"></div>
                <div name="reaction" class="reaction" data-reaction="cry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/sad.gif"></div>
                <div name="reaction" class="reaction" data-reaction="haha"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/haha.gif"></div>
                <div name="reaction" class="reaction" data-reaction="wow"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/woow.gif"></div>
                <div name="reaction" class="reaction" data-reaction="angry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/angry.gif"></div>
            </div>
            </div>
            </li>';
            $stmt = $port_db->prepare("
                SELECT reaction_type 
                FROM tbl_reaction 
                WHERE post_id = ?
                AND reaction_by <> ?
                GROUP BY reaction_type
            ");
            $stmt->execute([$row['ann_id'], $user_id]);
            $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($reactions as $react) {
                if ($react['reaction_type'] == 'like') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'heart') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'eey') {
                    echo '<li><a href="#"><img src="https://i.pinimg.com/564x/cc/12/e0/cc12e02e7eed4491de74e05ea8a019a5.jpg" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'love') {
                    echo '<li><a href="#"><img src="https://i.pinimg.com/564x/1e/b9/ab/1eb9abce88c9859c08e70330ef8495dc.jpg" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'cry') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'haha') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'wow') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'angry') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
            }


            if ($reaction !== false && isset($reaction['reaction_count'])) {
                echo '
                <li><a><span>' . htmlspecialchars($reaction['reaction_count']) . '</span></a>
                    <div class="tooltip">Tooltip for Item 1</div>
                </li>';
            } else {
                echo '<li><a><span></span></a></li>';
            }
            echo '</ul>';
            echo '<ul class="float-right">';
            $stmt = $port_db->prepare("
                SELECT com_post_id, COUNT(*) AS comment_count 
                FROM tbl_post_comment 
                WHERE com_post_id = ?
                GROUP BY com_post_id
                
            ");
            $stmt->execute([$row['ann_id']]);
            $cm = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $port_db->prepare("
                SELECT comment_onid, COUNT(*) AS counts 
                FROM tbl_comment 
                WHERE comment_onid = ?
                GROUP BY comment_onid
            ");
            $stmt->execute([$row['ann_id']]);
            $comcount = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalComments = 0;
            if ($cm !== false && isset($cm['comment_count'])) {
                $totalComments += $cm['comment_count'];
            }
            if ($comcount !== false && isset($comcount['counts'])) {
                $totalComments += $comcount['counts'];
            }
            
            if ($totalComments > 0) {
                echo '<li><a style="cursor: pointer;" data-toggle="modal" data-target="#comment-Modal' . htmlspecialchars($row['ann_id']) . '">
                        <span>' . htmlspecialchars($totalComments) . ' <i class="ti-comment"></i></span>
                      </a></li>';
            } else {
                echo '<li><a><span></span></a></li>';
            }
            echo '</ul>';
            echo '</div>';
            
            // Fetch comments from both databases and merge them
            $comments = [];
            $port_db->exec("SET NAMES 'utf8mb4'");
            $hr_db->exec("SET NAMES 'utf8mb4'");
            
            $stmt = $port_db->prepare("
                SELECT 
                  a.*, b.bi_empfname, b.bi_emplname, b.bi_empno
                FROM
                  tbl_post_comment a
                LEFT JOIN tbl201_basicinfo b
                ON b.bi_empno = a.com_post_by
                WHERE a.com_post_id = ?
                GROUP BY com_post_id,com_post_by,com_content 
                ORDER BY a.`com_date` DESC
                LIMIT 3
            ");
            $stmt->execute([$row['ann_id']]);
            $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            $stmt = $port_db->prepare("
                SELECT 
                  bi_empno,
                  comment_onid AS com_post_id,
                  comment_content AS com_content,
                  comment_datetime AS com_date,
                  bi_empno,
                  bi_empfname,
                  bi_emplname
                FROM tbl_comment
                LEFT JOIN tbl_announcement ON ann_id = comment_onid
                LEFT JOIN tbl_user2 ON U_ID = comment_by
                LEFT JOIN tbl201_basicinfo ON bi_empno = Emp_No
                WHERE comment_onid = ?
                AND comment_stat = 'active'
                GROUP BY bi_empno,comment_onid,comment_content
                ORDER BY comment_datetime DESC
                LIMIT 3
            ");
            $stmt->execute([$row['ann_id']]);
            $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Sort comments by date
            usort($comments, function($a, $b) {
                return strtotime($a['com_date']) - strtotime($b['com_date']);
            });
            
            // Loop through comments and display them
            if (!empty($comments)) {
            foreach ($comments as $c) {
                echo '<div class="cardbox-base-comment">';
                echo '<div class="media m-1">';
                echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
                echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($c['bi_empno']) . '.JPG" alt="User"></a>';
                echo '</div>';
                echo '<div class="media-body">';
                echo '<p class="m-0">' . htmlspecialchars($c['bi_empfname']) . ' ' . htmlspecialchars($c['bi_emplname']) . '</p>';
                echo '<small><span><i class="icon ion-md-pin"></i> ' . htmlspecialchars_decode($c['com_content'])  . '</span></small>';

                try {
                    $timezone = new DateTimeZone('Asia/Manila');
                    
                    $commentTime = new DateTime($c['com_date'], $timezone);
                    $currentTime = new DateTime('now', $timezone);
                
                    $interval = $commentTime->diff($currentTime);
                
                    if ($interval->y > 0) {
                        $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->m > 0) {
                        $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->d > 0) {
                        $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->h > 0) {
                        $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->i > 0) {
                        $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->s > 0) {
                        $timeAgo = $interval->s . ' second' . ($interval->s > 1 ? 's' : '') . ' ago';
                    } else {
                        $timeAgo = 'Just now';
                    }
                } catch (Exception $e) {
                    $timeAgo = 'Invalid date';
                }



                echo '<div class="comment-reply">
                        <small><a>' . htmlspecialchars($timeAgo) . '</a></small>
                      </div>';
                echo '</div>'; // Close media-body
                echo '</div>'; // Close media
                echo '</div>'; // Close cardbox-base-comment
                }
            }
            echo '<div id="comment-section"></div>';
            


            // Add new comment input section
            echo '<div class="cardbox-base-comment">';
            echo '<div class="media m-1">';
            echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
            echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($user_id) . '.JPG" alt="User"></a>';
            echo '</div>';
            echo '<div class="media-body" id="comment">';
            echo '<div class="textarea-wrapper">';
            echo '<input type="hidden" name="com-id" value="' . htmlspecialchars($row['ann_id']) . '" />';
            echo '<div class="textarea-wrapper">';
            echo '<input type="text" name="Mycomm-' . htmlspecialchars($row['ann_id']) . '" placeholder="Write a comment..." id="Mycomment-' . htmlspecialchars($row['ann_id']) . '" class="emojiable-option"></input>';
            echo '<div id="mention-list-' . htmlspecialchars($row['ann_id']) . '" class="mention-list"></div>';
            echo '</div>';
            echo '<i class="ti-face-smile icon emoji-icon" onclick="showDiv(' . $row['ann_id'] . ')"></i>';
            echo '</div>'; // Close textarea-wrapper
            echo '<a href="#" id="saveComment-' . htmlspecialchars($row['ann_id']) . '" onclick="saveComment(' . htmlspecialchars($row['ann_id']) . '); return false;"><img src="assets/img/send_icon.png" height="30" width="30"/></a>';
            echo '</div>'; // Close media-body
            echo '</div>'; // Close media
            echo '</div>'; // Close cardbox-base-comment

            // Add new comment input section
            echo '<div class="cardbox-base-comment" id="hidden-div-' . $row['ann_id'] . '" style="width:100%; display:none;">';
            echo ' <div class="emoji-tabs">
                        <ul class="nav nav-tabs  tabs" role="tablist" style="display: inherit !important;height:40px;">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#face-' . $row['ann_id'] . '" role="tab">&#128578;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#heart-' . $row['ann_id'] . '" role="tab">&#129293;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#food-' . $row['ann_id'] . '" role="tab">&#127860;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#plant-' . $row['ann_id'] . '" role="tab">&#127808;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#weather-' . $row['ann_id'] . '" role="tab">&#127759;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#symbols-' . $row['ann_id'] . '" role="tab">&#127881;</a>
                            </li>
                        </ul>
                        <div class="tab-content tabs card-block" style="padding:10px;font-size:18px;">
                            <div class="tab-pane active" id="face-' . $row['ann_id'] . '" role="tabpanel">';
                                $faces = [
                                    '&#128512;', '&#128513;', '&#128514;', '&#128515;', '&#128516;', '&#128517;', '&#128518;', '&#128519;',
                                    '&#129392;',
                                    '&#129297;', '&#129303;', '&#129312;', '&#129319;', '&#129321;', '&#129395;', '&#129392;', '&#129327;',
                                    '&#128520;', '&#128521;', '&#128522;', '&#128523;', '&#128524;', '&#128525;', '&#128526;', '&#128527;',
                                    '&#128528;', '&#128529;', '&#128530;', '&#128531;', '&#128532;', '&#128533;', '&#128534;', '&#128535;',
                                    '&#128536;', '&#128537;', '&#128538;', '&#128539;', '&#128540;', '&#128541;', '&#128542;', '&#128543;',
                                    '&#128544;', '&#128545;', '&#128546;', '&#128547;', '&#128548;', '&#128549;', '&#128550;', '&#128551;',
                                    '&#128552;', '&#128553;', '&#128554;', '&#128555;', '&#128556;', '&#128557;', '&#128558;', '&#128559;',
                                    '&#128560;', '&#128561;', '&#128562;', '&#128563;', '&#128564;', '&#128565;', '&#128566;', '&#128567;',
                                    '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;','&#128400;'
                                ];
                                
                                foreach ($faces as $fc) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($fc) . '\')">' . htmlspecialchars_decode($fc) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="heart-' . $row['ann_id'] . '" role="tabpanel">';
                                $heart = [ 
                                    '&#10084;','&#128140;','&#10083;',
                                    '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', 
                                    '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;'
                                ];
                                
                                foreach ($heart as $hrt) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($hrt) . '\')">' . htmlspecialchars_decode($hrt) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="food-' . $row['ann_id'] . '" role="tabpanel">';
                                $food = [ 
                                    '&#127838;','&#129360;','&#129366;','&#129391;','&#129374;','&#129479;','&#129472;','&#127830;',
                                    '&#127831;','&#129385;','&#127828;','&#127839;','&#127789;','&#127829;','&#129386;','&#129747;',
                                    '&#127790;','&#127791;','&#129372;','&#129478;','&#127837;','&#127836;','&#127829;','&#129368;',
                                    '&#129367;','&#127835;','&#127834;','&#127843;','&#127844;','&#127845;','&#129382;','&#129748;',
                                    '&#127846;','&#127847;','&#127848;','&#127849;','&#127850;','&#127874;','&#127856;','&#129473;',
                                    '&#129383;','&#127851;','&#127852;','&#127853;','&#127854;','&#127855;','&#128006;','&#9749;',
                                    '&#129749;','&#127861;','&#127862;','&#127867;','&#127863;','&#127864;','&#127865;','&#127866;',
                                    '&#127867;','&#129380;','&#129749;','&#127860;','&#129379;','&#127869;','&#129475;',
                                ];
                                
                                foreach ($food as $fd) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($fd) . '\')">' . htmlspecialchars_decode($fd) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="plant-' . $row['ann_id'] . '" role="tabpanel">';
                                $plant = [ 
                                    '&#127793;','&#127794;','&#127795;','&#127796;','&#127797;','&#127806;','&#127807;','&#9752;',
                                    '&#127808;','&#127809;','&#127810;','&#127811;','&#127799;','&#127800;','&#127801;','&#129344;',
                                    '&#127802;','&#127803;','&#127804;','&#127806;','&#127805;','&#127807;','&#127812;','&#127883;',
                                    '&#127885;','&#127815;','&#127816;','&#127817;','&#127818;','&#127819;','&#127820;','&#127821;',
                                    '&#129389;','&#127822;','&#127823;','&#127824;','&#127825;','&#127826;','&#127827;','&#129744;',
                                    '&#129373;','&#127813;','&#129381;','&#129361;','&#127814;','&#129364;','&#129365;','&#127805;',
                                    '&#129362;','&#129388;','&#129382;','&#129476;','&#129477;','&#127812;','&#129745;'
                                ];
                                
                                foreach ($plant as $plt) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($plt) . '\')">' . htmlspecialchars_decode($plt) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="weather-' . $row['ann_id'] . '" role="tabpanel">';
                                $weather = [ 
                                    '&#9728;','&#127774;','&#9925;','&#127775;','&#127776;','&#127777;','&#127778;','&#127779;',
                                    '&#9928;','&#127786;','&#127787;','&#127788;','&#10052;','&#9731;','&#127777;','&#127752;',
                                    '&#9889;','&#127746;','&#9730;','&#128168;','&#127756;','&#127775;','&#127769;','&#127762;',
                                    '&#127761;','&#11088;','&#9732;','&#127765;','&#127766;','&#127767;','&#127768;','&#127763;',
                                    '&#127764;','&#127757;','&#127758;','&#127759;','&#129680;'
                                ];
                                
                                foreach ($weather as $weath) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($weath) . '\')">' . htmlspecialchars_decode($weath) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="symbols-' . $row['ann_id'] . '" role="tabpanel">';
                                $symbols = [ 
                                    '&#127881;','&#127882;','&#129395;','&#127880;','&#127874;','&#127873;','&#129665;','&#129681;',
                                    '&#127879;','&#127878;','&#129512;','&#10024;','&#127775;','&#128171;','&#127925;','&#127926;',
                                    '&#127908;','&#127911;','&#129668;','&#127942;','&#127935;','&#129351;','&#129352;','&#129353;',
                                    '&#10013;','&#9770;','&#9784;','&#9775;','&#10017;','&#128303;','&#128329;','&#128720;',
                                    '&#128334;','&#9774;','&#129418;','&#9851;','&#9884;','&#9888;','&#128696;','&#9940;',
                                    '&#128683;','&#10060;','&#10004;','&#128308;','&#128309;','&#9898;','&#9899;','&#128312;',
                                    '&#128311;'
                                ];
                                
                                foreach ($symbols as $sym) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($sym) . '\')">' . htmlspecialchars_decode($sym) . '</span>';
                                }
                        echo '</div>
                        </div>
                    </div>';
            // echo '<div class="m-1">';
            // $emojis = [ 
            //        '&#127872;', '&#127873;', '&#127874;', '&#127878;', '&#127879;', '&#127880;', '&#127881;', '&#127882;', 
            //        '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', 
            //        '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;',
            //        '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;','&#128400;'
            //    ];
               
            //    foreach ($emojis as $emoji) {
            //        echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($emoji) . '\')">' . htmlspecialchars_decode($emoji) . '</span>';
            //    }
            // echo '</div>';
            echo '</div>';

            // Add new comment input section
            echo '</div>'; // Close cardbox
            echo '</section>'; // Close profile-feed

            echo '<div class="modal fade" id="comment-Modal' . htmlspecialchars($row['ann_id']) . '" tabindex="-1" role="dialog" style="height:100vh;overflow:hidden;">
                    <div class="modal-dialog modal-lg" role="document"style="margin-top:0px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">'. htmlspecialchars($row['bi_empfname']) . ' '. htmlspecialchars($row['bi_emplname']).' Post</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa fa-times-circle"style="font-size:24px;"></i></span>
                                </button>
                            </div>
                            <div class="modal-body" id="post-modalbody" style="height:480px;overflow:auto;">';
                                // Cardbox Item
                                echo '<div class="cardbox-item">';
                                if(strpos($row['ann_content'], '<figure') !== false){

                                    $imagePattern = '/<img\s+[^>]*src=["\']([^"\']+)["\']/i';

                                    // Match image sources
                                    preg_match_all($imagePattern, $row['ann_content'], $imageMatches);
                                    $sources = $imageMatches[1];

                                    foreach ($sources as $imgv) {
                                        echo '<img class="img-fluid" style="max-height: 500px !important;cursor: pointer;" src="https://teamtngc.com/hris2/pages/announcement/' . htmlspecialchars($imgv) . '">';
                                    }

                                }else if(strpos($row['ann_content'], '<figure') !== false){
                                    echo str_replace('../announcement', 'https://teamtngc.com/hris2/pages/announcement', $row['ann_content']);
                                }else{
                                    echo '<img class="img-fluid" style="max-height: 500px !important;cursor: pointer;" src="https://teamtngc.com/hris2/pages/announcement/' . htmlspecialchars($row['ann_content']) . '">';
                                }
                                // echo "".$row['ann_content']."";
                                echo '</div>'; // Close cardbox-item
                                // Cardbox Base
                                echo '<div class="cardbox-base">';
                                echo '<ul>';
                                echo '<li>';
                                echo '<div class="reaction-container">';
                                $stmt = $port_db->prepare("
                                    SELECT reaction_type FROM tbl_reaction 
                                    WHERE reaction_by = ?
                                    AND post_id = ?
                                    GROUP BY reaction_type
                                ");
                                $stmt->execute([$user_id,$row['ann_id']]);
                                $ireact = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                // Check if the reaction type is 'heart'
                                if ($ireact && $ireact['reaction_type'] == 'heart') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'like') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'love') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'eey') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="https://i.pinimg.com/564x/cc/12/e0/cc12e02e7eed4491de74e05ea8a019a5.jpg" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'cry') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'haha') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'wow') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'angry') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/sadness.WEBP class="img-fluid rounded-circle">
                                          </a>';
                                } else {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <i class="ti-face-smile"></i>
                                          </a>';
                                }

                                 echo '<input type="hidden" name="post-id" value="' .htmlspecialchars($row['ann_id']). '" />
                                <div class="reaction-options">
                                    <div name="reaction" class="reaction" data-reaction="like"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/like.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="eey"><img width="50" height="60" src="https://i.pinimg.com/originals/58/91/52/58915204d17860c24d4c02be7425a830.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="heart"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/heart.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="love"><img class="img" width="50" height="50" src="https://media1.tenor.com/m/63nE7vC84pIAAAAd/care-discord.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="cry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/sad.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="haha"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/haha.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="wow"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/woow.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="angry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/angry.gif"></div>
                                </div>
                                </div>
                                </li>';
                                $stmt = $port_db->prepare("
                                    SELECT reaction_type 
                                    FROM tbl_reaction 
                                    WHERE post_id = ?
                                    AND reaction_by <> ?
                                    GROUP BY reaction_type
                                ");
                                $stmt->execute([$row['port_id'], $user_id]);
                                $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($reactions as $react) {
                                    if ($react['reaction_type'] == 'like') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'heart') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'eey') {
                                        echo '<li><a href="#"><img src="https://i.pinimg.com/564x/9d/04/2c/9d042cb030e250961454adf7131f76b5.jpg" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'love') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'cry') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'haha') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'wow') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'angry') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                }


                                if ($reaction !== false && isset($reaction['reaction_count'])) {
                                    echo '<li><a><span>' . htmlspecialchars($reaction['reaction_count']) . '</span></a></li>';
                                } else {
                                    echo '<li><a><span></span></a></li>';
                                }
                                echo '</ul>';
                                echo '<ul class="float-right">';
                                $stmt = $port_db->prepare("
                                    SELECT com_post_id, COUNT(*) AS comment_count 
                                    FROM tbl_post_comment 
                                    WHERE com_post_id = ?
                                    GROUP BY com_post_id
                                    
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $cm = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                $stmt = $port_db->prepare("
                                    SELECT comment_onid, COUNT(*) AS counts 
                                    FROM tbl_comment 
                                    WHERE comment_onid = ?
                                    GROUP BY comment_onid
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $comcount = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                $totalComments = 0;
                                if ($cm !== false && isset($cm['comment_count'])) {
                                    $totalComments += $cm['comment_count'];
                                }
                                if ($comcount !== false && isset($comcount['counts'])) {
                                    $totalComments += $comcount['counts'];
                                }
                                
                                if ($totalComments > 0) {
                                    echo '<li><a style="cursor: pointer;" data-toggle="modal" data-target="#comment-Modal' . htmlspecialchars($row['ann_id']) . '">
                                            <span>' . htmlspecialchars($totalComments) . ' <i class="ti-comment"></i></span>
                                          </a></li>';
                                } else {
                                    echo '<li><a><span></span></a></li>';
                                }
                                echo '</ul>';
                                echo '</div>';
                                
                                // Fetch comments from both databases and merge them
                                $comments = [];
                                
                                $stmt = $port_db->prepare("
                                    SELECT 
                                      a.*, b.bi_empfname, b.bi_emplname, b.bi_empno
                                    FROM
                                      tbl_post_comment a
                                    LEFT JOIN tbl201_basicinfo b
                                    ON b.bi_empno = a.com_post_by
                                    WHERE a.com_post_id = ?
                                    GROUP BY com_post_id, com_post_by, com_content
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
                                
                                $stmt = $port_db->prepare("
                                    SELECT 
                                      bi_empno,
                                      comment_onid AS com_post_id,
                                      comment_content AS com_content,
                                      comment_datetime AS com_date,
                                      bi_empno,
                                      bi_empfname,
                                      bi_emplname
                                    FROM tbl_comment
                                    LEFT JOIN tbl_announcement ON ann_id = comment_onid
                                    LEFT JOIN tbl_user2 ON U_ID = comment_by
                                    LEFT JOIN tbl201_basicinfo ON bi_empno = Emp_No
                                    WHERE comment_onid = ?
                                    GROUP BY bi_empno,comment_onid,comment_content
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
                                
                                // Sort comments by date
                                usort($comments, function($a, $b) {
                                    return strtotime($a['com_date']) - strtotime($b['com_date']);
                                });
                                
                                // Loop through comments and display them
                                if (!empty($comments)) {
                                foreach ($comments as $c) {

                                    echo '<div class="cardbox-base-comment">';
                                    echo '<div class="media m-1">';
                                    echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
                                    echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($c['bi_empno']) . '.JPG" alt="User"></a>';
                                    echo '</div>';
                                    echo '<div class="media-body">';
                                    echo '<p class="m-0">' . htmlspecialchars($c['bi_empfname']) . ' ' . htmlspecialchars($c['bi_emplname']) . '</p>';
                                    echo '<small><span><i class="icon ion-md-pin"></i> ' . htmlspecialchars_decode($c['com_content']) . '</span></small>';

                                    try {
                                        // Set the timezone explicitly
                                        $timezone = new DateTimeZone('Asia/Manila'); // Adjust to your timezone
                                    
                                        $commentTime = new DateTime($c['com_date'], $timezone);
                                        $currentTime = new DateTime('now', $timezone); // Use the same timezone
                                    
                                        $interval = $commentTime->diff($currentTime);
                                    
                                        // Generate "time ago" string
                                        if ($interval->y > 0) {
                                            $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->m > 0) {
                                            $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->d > 0) {
                                            $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->h > 0) {
                                            $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->i > 0) {
                                            $timeAgo = $interval->i . ' min' . ($interval->i > 1 ? 's' : '') . ' ago';
                                        } else {
                                            $timeAgo = 'Just now';
                                        }
                                    } catch (Exception $e) {
                                        $timeAgo = 'Invalid date'; // Fallback in case of parsing issues
                                    }

                                    echo '<div class="comment-reply">
                                            <small><a>' . htmlspecialchars($timeAgo) . '</a></small>
                                          </div>';
                                    echo '</div>'; // Close media-body
                                    echo '</div>'; // Close media
                                    echo '</div>'; // Close cardbox-base-comment
                                    }
                                }
                                // <small><a style="cursor: pointer;">Reply</a></small>

                                echo '<div id="comment-section"></div>';

                         echo '</div>';
                         echo '<div class="modal-footer">';
                         // Add new comment input section
                         echo '<div class="cardbox-base-comment" id="hidden-div" style="width:100%;">';
                         echo '<div class="media m-1">';
                         echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
                         echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($user_id) . '.JPG" alt="User"></a>';
                         echo '</div>';
                         echo '<div class="media-body" id="comment">';
                         echo '<div class="textarea-wrapper">';
                         echo '<input type="hidden" name="com-id" value="' . htmlspecialchars($row['ann_id']) . '" />';
                         echo '<input type="text" name="SMycomment-' . htmlspecialchars($row['ann_id']) . '" placeholder="Write a comment..." id="SMycomment-' . htmlspecialchars($row['ann_id']) . '" class="emojiable-option"></input>';
                         echo '<i class="ti-face-smile icon emoji-icon" onclick="showEmoji(' . $row['ann_id'] . ')"></i>';
                         echo '</div>'; // Close textarea-wrapper
                         echo '<a href="#" id="saveComment-' . htmlspecialchars($row['ann_id']) . '" onclick="saveComment(' . htmlspecialchars($row['ann_id']) . '); return false;"><img src="assets/img/send_icon.png" height="30" width="30"/></a>';
                         echo '</div>'; // Close media-body
                         echo '</div>'; // Close media
                         echo '</div>'; // Close cardbox-base-comment
                         //Add new comment input section
                               
                        echo '</div>';
                        //footer end

                        echo '<div class="modal-footer">';
                         // Add new comment input section
                         echo '<div class="cardbox-base-comment" id="emoji-div-' . $row['ann_id'] . '" style="width:100%; display:none;">';
                         echo '<div class="m-1">';
                         $emojis = [
                                '&#128512;', '&#128513;', '&#128514;', '&#128515;', '&#128516;', '&#128517;', '&#128518;', '&#128519;',
                   
                                '&#129297;', '&#129303;', '&#129312;', '&#129319;', '&#129321;', '&#129395;', '&#129392;', '&#129327;',
                                '&#128520;', '&#128521;', '&#128522;', '&#128523;', '&#128524;', '&#128525;', '&#128526;', '&#128527;',
                                '&#128528;', '&#128529;', '&#128530;', '&#128531;', '&#128532;', '&#128533;', '&#128534;', '&#128535;',
                                '&#128536;', '&#128537;', '&#128538;', '&#128539;', '&#128540;', '&#128541;', '&#128542;', '&#128543;',
                                '&#128544;', '&#128545;', '&#128546;', '&#128547;', '&#128548;', '&#128549;', '&#128550;', '&#128551;',
                                '&#128552;', '&#128553;', '&#128554;', '&#128555;', '&#128556;', '&#128557;', '&#128558;', '&#128559;',
                                '&#128560;', '&#128561;', '&#128562;', '&#128563;', '&#128564;', '&#128565;', '&#128566;', '&#128567;', 
                                '&#127872;', '&#127873;', '&#127874;', '&#127878;', '&#127879;', '&#127880;', '&#127881;', '&#127882;', 
                                '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', 
                                '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;',
                                '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;','&#128400;'
                            ];
                            
                            foreach ($emojis as $emoji) {
                                echo '<span class="emoji" onclick="insertEmoji(\'SMycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($emoji) . '\')">' . $emoji . '</span>';

                            }
                         echo '</div>';
                         echo '</div>';      
                        echo '</div>';
                        //footer end

                     echo '</div>';
                echo '</div>';
            echo '</div>';
            }else {

            //SIMPLE TEXT POST
            echo '<section class="profile-feed" id="prof-'. htmlspecialchars($row['ann_id']) .'">';
            echo '<div class="cardbox shadow-lg bg-white">';
            echo '<div class="cardbox-heading">';
            echo '<div class="dropdown float-right">';
            echo '<button class="btn btn-flat btn-flat-icon" type="button" data-toggle="dropdown" aria-expanded="false">';
            echo '<em class="fa fa-ellipsis-h"></em>';
            echo '</button>';
            echo '<div class="dropdown-menu dropdown-scale dropdown-menu-right" role="menu">';
            echo '<a class="dropdown-item" href="#" onclick="hideProfile('. htmlspecialchars($row['ann_id']) .'); return false;"><i class="fas fa-eye-slash"></i> Hide post</a>';
            // echo '<a class="dropdown-item" href="#">Stop following</a>';
            echo '<a class="dropdown-item" href="#" data-toggle="modal" data-target="#report'. htmlspecialchars($row['ann_id']) .'" style="color:red;"><i class="fas fa-exclamation-circle"></i> Report</a>';
            echo '</div>';
            echo '</div>'; // Close dropdown
            echo '<div class="modal fade" id="report'. htmlspecialchars($row['ann_id']) .'" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <input type="hidden" name="postid" value="'. htmlspecialchars($row['ann_id']) .'">
                            <div class="modal-header">
                                <h4 class="modal-title" style="text-align: center !important;">Report '. htmlspecialchars($row['bi_empfname']) . ' '. htmlspecialchars($row['bi_emplname']).' Post</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa fa-times-circle" style="font-size:24px;"></i></span>
                                </button>
                            </div>
                            <div class="modal-body" style="padding: 10px !important;">
                                <div style="display: flex;">
                                    <label style="margin-right: 15px;">Reason:</label>
                                    <textarea name="reason" rows="5" cols="5" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default waves-effect btn-mini" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light btn-mini report-btn" data-postid="'. htmlspecialchars($row['ann_id']) .'">Report</button>
                            </div>
                        </div>
                    </div>
                </div>';
            echo '<div class="media m-0">';
            echo '<div class="d-flex mr-3">';
            echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/'. htmlspecialchars($row['bi_empno']) .'.jpg'.'" onerror="this.onerror=null; this.src="https://i.pinimg.com/736x/6e/db/e7/6edbe770213e7d6885240b2c91e9dd86.jpg";"></a>';
            echo '</div>';
            echo '<div class="media-body">';
            echo '<p class="m-0" style="font-weight: 500px;">'. htmlspecialchars($row['bi_empfname']) . ' '. htmlspecialchars($row['bi_emplname']).'</p>';
            echo '<small><span><i class="icon ion-md-pin"></i>' . date("F j, Y", strtotime($row['ann_timestatmp'])) . '</span></small>';
            echo '<small><span><i class="icon ion-md-time"></i>' . (new DateTime($row['ann_timestatmp']))->format("h:i A") . '</span></small>';
            echo '</div>'; // Close media-body
            echo '</div>'; // Close media

            // echo '<div class="media m-0">';
            // echo '<div class="media-body">';
            // echo '<p><span><i class="icon ion-md-time"></i>' . htmlspecialchars($row['ann_title']). '</span></p>';
            // echo '</div>'; // Close media-body
            // echo '</div>'; // Close media

            echo '</div>'; // Close cardbox-heading
            
            // Cardbox Item
            echo '<div class="cardbox-item" style="padding:20px !important;color:#000000;">';
            echo '<span>' . htmlspecialchars($row['ann_title']). '</span>';
            echo '</div>'; // Close cardbox-item
            
            // Cardbox Base
            echo '<div class="cardbox-base">';
            echo '<ul>';
            echo '<li>';
            echo '<div class="reaction-container">';
            $stmt = $port_db->prepare("
                SELECT reaction_type FROM tbl_reaction 
                WHERE reaction_by = ?
                AND post_id = ?
                GROUP BY reaction_type
            ");
            $stmt->execute([$user_id,$row['ann_id']]);
            $ireact = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the reaction type is 'heart'
            if ($ireact && $ireact['reaction_type'] == 'heart') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'like') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'love') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'eey') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="https://i.pinimg.com/564x/9d/04/2c/9d042cb030e250961454adf7131f76b5.jpg" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'cry') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'haha') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'wow') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle">
                      </a>';
            }elseif ($ireact && $ireact['reaction_type'] == 'angry') {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle">
                      </a>';
            } else {
                echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                        <i class="ti-face-smile"></i>
                      </a>';
            }

             echo '<input type="hidden" name="post-id" value="' .htmlspecialchars($row['ann_id']). '" />
            <div class="reaction-options">
                <div name="reaction" class="reaction" data-reaction="like"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/like.gif"></div>
                <div name="reaction" class="reaction" data-reaction="eey"><img width="50" height="60" src="https://i.pinimg.com/originals/58/91/52/58915204d17860c24d4c02be7425a830.gif"></div>
                <div name="reaction" class="reaction" data-reaction="heart"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/heart.gif"></div>
                <div name="reaction" class="reaction" data-reaction="love"><img class="img" width="50" height="50" src="https://media1.tenor.com/m/63nE7vC84pIAAAAd/care-discord.gif"></div>
                <div name="reaction" class="reaction" data-reaction="cry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/sad.gif"></div>
                <div name="reaction" class="reaction" data-reaction="haha"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/haha.gif"></div>
                <div name="reaction" class="reaction" data-reaction="wow"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/woow.gif"></div>
                <div name="reaction" class="reaction" data-reaction="angry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/angry.gif"></div>
            </div>
            </div>
            </li>';
            $stmt = $port_db->prepare("
                SELECT reaction_type 
                FROM tbl_reaction 
                WHERE post_id = ?
                AND reaction_by <> ?
                GROUP BY reaction_type
            ");
            $stmt->execute([$row['port_id'], $user_id]);
            $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($reactions as $react) {
                if ($react['reaction_type'] == 'like') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'heart') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'eey') {
                    echo '<li><a href="#"><img src="https://i.pinimg.com/564x/cc/12/e0/cc12e02e7eed4491de74e05ea8a019a5.jpg" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'love') {
                    echo '<li><a href="#"><img src="https://i.pinimg.com/564x/1e/b9/ab/1eb9abce88c9859c08e70330ef8495dc.jpg" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'cry') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'haha') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'wow') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
                if ($react['reaction_type'] == 'angry') {
                    echo '<li><a href="#"><img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle"></a></li>';
                }
            }


            if ($reactions !== false && isset($reactions['reaction_count'])) {
                echo '
                <li><a><span>' . htmlspecialchars($reactions['reaction_count']) . '</span></a>
                    <div class="tooltip">Tooltip for Item 1</div>
                </li>';
            } else {
                echo '<li><a><span></span></a></li>';
            }
            echo '</ul>';
            echo '<ul class="float-right">';
            $stmt = $port_db->prepare("
                SELECT com_post_id, COUNT(*) AS comment_count 
                FROM tbl_post_comment 
                WHERE com_post_id = ?
                GROUP BY com_post_id
                
            ");
            $stmt->execute([$row['ann_id']]);
            $cm = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $port_db->prepare("
                SELECT comment_onid, COUNT(*) AS counts 
                FROM tbl_comment 
                WHERE comment_onid = ?
                GROUP BY comment_onid
            ");
            $stmt->execute([$row['ann_id']]);
            $comcount = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalComments = 0;
            if ($cm !== false && isset($cm['comment_count'])) {
                $totalComments += $cm['comment_count'];
            }
            if ($comcount !== false && isset($comcount['counts'])) {
                $totalComments += $comcount['counts'];
            }
            
            if ($totalComments > 0) {
                echo '<li><a style="cursor: pointer;" data-toggle="modal" data-target="#comment-Modal' . htmlspecialchars($row['ann_id']) . '">
                        <span>' . htmlspecialchars($totalComments) . ' <i class="ti-comment"></i></span>
                      </a></li>';
            } else {
                echo '<li><a><span></span></a></li>';
            }
            echo '</ul>';
            echo '</div>';
            
            // Fetch comments from both databases and merge them
            $comments = [];
            $port_db->exec("SET NAMES 'utf8mb4'");
            $hr_db->exec("SET NAMES 'utf8mb4'");
            
            $stmt = $port_db->prepare("
                SELECT 
                  a.*, b.bi_empfname, b.bi_emplname, b.bi_empno
                FROM
                  tbl_post_comment a
                LEFT JOIN tbl201_basicinfo b
                ON b.bi_empno = a.com_post_by
                WHERE a.com_post_id = ? 
                ORDER BY a.`com_date` DESC
                LIMIT 3
            ");
            $stmt->execute([$row['ann_id']]);
            $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            $stmt = $port_db->prepare("
                SELECT 
                  bi_empno,
                  comment_onid AS com_post_id,
                  comment_content AS com_content,
                  comment_datetime AS com_date,
                  bi_empno,
                  bi_empfname,
                  bi_emplname
                FROM tbl_comment
                LEFT JOIN tbl_announcement ON ann_id = comment_onid
                LEFT JOIN tbl_user2 ON U_ID = comment_by
                LEFT JOIN tbl201_basicinfo ON bi_empno = Emp_No
                WHERE comment_onid = ?
                GROUP BY bi_empno,comment_onid,comment_content
                ORDER BY comment_datetime DESC
                LIMIT 3
            ");
            $stmt->execute([$row['ann_id']]);
            $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Sort comments by date
            usort($comments, function($a, $b) {
                return strtotime($a['com_date']) - strtotime($b['com_date']);
            });
            
            // Loop through comments and display them
            if (!empty($comments)) {
            foreach ($comments as $c) {
                echo '<div class="cardbox-base-comment">';
                echo '<div class="media m-1">';
                echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
                echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($c['bi_empno']) . '.JPG" alt="User"></a>';
                echo '</div>';
                echo '<div class="media-body">';
                echo '<p class="m-0">' . htmlspecialchars($c['bi_empfname']) . ' ' . htmlspecialchars($c['bi_emplname']) . '</p>';
                echo '<small><span><i class="icon ion-md-pin"></i> ' . htmlspecialchars_decode($c['com_content'])  . '</span></small>';

                try {
                    // Set the timezone explicitly
                    $timezone = new DateTimeZone('Asia/Manila'); // Adjust to your timezone
                
                    $commentTime = new DateTime($c['com_date'], $timezone);
                    $currentTime = new DateTime('now', $timezone); // Use the same timezone
                
                    $interval = $commentTime->diff($currentTime);
                
                    // Generate "time ago" string
                    if ($interval->y > 0) {
                        $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->m > 0) {
                        $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->d > 0) {
                        $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->h > 0) {
                        $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->i > 0) {
                        $timeAgo = $interval->i . ' min' . ($interval->i > 1 ? 's' : '') . ' ago';
                    } else {
                        $timeAgo = 'Just now';
                    }
                } catch (Exception $e) {
                    $timeAgo = 'Invalid date'; // Fallback in case of parsing issues
                }


                echo '<div class="comment-reply">
                        <small><a>' . htmlspecialchars($timeAgo) . '</a></small>
                      </div>';
                echo '</div>'; // Close media-body
                echo '</div>'; // Close media
                echo '</div>'; // Close cardbox-base-comment
                }
            }
            echo '<div id="comment-section"></div>';
            


            // Add new comment input section
            echo '<div class="cardbox-base-comment">';
            echo '<div class="media m-1">';
            echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
            echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($user_id) . '.JPG" alt="User"></a>';
            echo '</div>';
            echo '<div class="media-body" id="comment">';
            echo '<div class="textarea-wrapper">';
            echo '<input type="hidden" name="com-id" value="' . htmlspecialchars($row['ann_id']) . '" />';
            echo '<input type="text" name="Mycomm-' . htmlspecialchars($row['ann_id']) . '" placeholder="Write a comment..." id="Mycomment-' . htmlspecialchars($row['ann_id']) . '" class="emojiable-option"></input>';
            echo '<i class="ti-face-smile icon emoji-icon" onclick="showDiv(' . $row['ann_id'] . ')"></i>';
            echo '</div>'; // Close textarea-wrapper
            echo '<a href="#" id="saveComment-' . htmlspecialchars($row['ann_id']) . '" onclick="saveComment(' . htmlspecialchars($row['ann_id']) . '); return false;"><img src="assets/img/send_icon.png" height="30" width="30"/></a>';
            echo '</div>'; // Close media-body
            echo '</div>'; // Close media
            echo '</div>'; // Close cardbox-base-comment

            // Add new comment input section
            echo '<div class="cardbox-base-comment" id="hidden-div-' . $row['ann_id'] . '" style="width:100%; display:none;">';
            echo ' <div class="emoji-tabs">
                        <ul class="nav nav-tabs  tabs" role="tablist" style="display: inherit !important;height:40px;">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#face-' . $row['ann_id'] . '" role="tab">&#128578;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#heart-' . $row['ann_id'] . '" role="tab">&#129293;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#food-' . $row['ann_id'] . '" role="tab">&#127860;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#plant-' . $row['ann_id'] . '" role="tab">&#127808;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#weather-' . $row['ann_id'] . '" role="tab">&#127759;</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#symbols-' . $row['ann_id'] . '" role="tab">&#127881;</a>
                            </li>
                        </ul>
                        <div class="tab-content tabs card-block" style="padding:10px;font-size:18px;">
                            <div class="tab-pane active" id="face-' . $row['ann_id'] . '" role="tabpanel">';
                                $faces = [
                                    '&#128512;', '&#128513;', '&#128514;', '&#128515;', '&#128516;', '&#128517;', '&#128518;', '&#128519;',
                                    '&#129392;',
                                    '&#129297;', '&#129303;', '&#129312;', '&#129319;', '&#129321;', '&#129395;', '&#129392;', '&#129327;',
                                    '&#128520;', '&#128521;', '&#128522;', '&#128523;', '&#128524;', '&#128525;', '&#128526;', '&#128527;',
                                    '&#128528;', '&#128529;', '&#128530;', '&#128531;', '&#128532;', '&#128533;', '&#128534;', '&#128535;',
                                    '&#128536;', '&#128537;', '&#128538;', '&#128539;', '&#128540;', '&#128541;', '&#128542;', '&#128543;',
                                    '&#128544;', '&#128545;', '&#128546;', '&#128547;', '&#128548;', '&#128549;', '&#128550;', '&#128551;',
                                    '&#128552;', '&#128553;', '&#128554;', '&#128555;', '&#128556;', '&#128557;', '&#128558;', '&#128559;',
                                    '&#128560;', '&#128561;', '&#128562;', '&#128563;', '&#128564;', '&#128565;', '&#128566;', '&#128567;',
                                    '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;','&#128400;'
                                ];
                                
                                foreach ($faces as $fc) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($fc) . '\')">' . htmlspecialchars_decode($fc) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="heart-' . $row['ann_id'] . '" role="tabpanel">';
                                $heart = [ 
                                    '&#10084;','&#128140;','&#10083;',
                                    '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', 
                                    '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;'
                                ];
                                
                                foreach ($heart as $hrt) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($hrt) . '\')">' . htmlspecialchars_decode($hrt) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="food-' . $row['ann_id'] . '" role="tabpanel">';
                                $food = [ 
                                    '&#127838;','&#129360;','&#129366;','&#129391;','&#129374;','&#129479;','&#129472;','&#127830;',
                                    '&#127831;','&#129385;','&#127828;','&#127839;','&#127789;','&#127829;','&#129386;','&#129747;',
                                    '&#127790;','&#127791;','&#129372;','&#129478;','&#127837;','&#127836;','&#127829;','&#129368;',
                                    '&#129367;','&#127835;','&#127834;','&#127843;','&#127844;','&#127845;','&#129382;','&#129748;',
                                    '&#127846;','&#127847;','&#127848;','&#127849;','&#127850;','&#127874;','&#127856;','&#129473;',
                                    '&#129383;','&#127851;','&#127852;','&#127853;','&#127854;','&#127855;','&#128006;','&#9749;',
                                    '&#129749;','&#127861;','&#127862;','&#127867;','&#127863;','&#127864;','&#127865;','&#127866;',
                                    '&#127867;','&#129380;','&#129749;','&#127860;','&#129379;','&#127869;','&#129475;',
                                ];
                                
                                foreach ($food as $fd) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($fd) . '\')">' . htmlspecialchars_decode($fd) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="plant-' . $row['ann_id'] . '" role="tabpanel">';
                                $plant = [ 
                                    '&#127793;','&#127794;','&#127795;','&#127796;','&#127797;','&#127806;','&#127807;','&#9752;',
                                    '&#127808;','&#127809;','&#127810;','&#127811;','&#127799;','&#127800;','&#127801;','&#129344;',
                                    '&#127802;','&#127803;','&#127804;','&#127806;','&#127805;','&#127807;','&#127812;','&#127883;',
                                    '&#127885;','&#127815;','&#127816;','&#127817;','&#127818;','&#127819;','&#127820;','&#127821;',
                                    '&#129389;','&#127822;','&#127823;','&#127824;','&#127825;','&#127826;','&#127827;','&#129744;',
                                    '&#129373;','&#127813;','&#129381;','&#129361;','&#127814;','&#129364;','&#129365;','&#127805;',
                                    '&#129362;','&#129388;','&#129382;','&#129476;','&#129477;','&#127812;','&#129745;'
                                ];
                                
                                foreach ($plant as $plt) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($plt) . '\')">' . htmlspecialchars_decode($plt) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="weather-' . $row['ann_id'] . '" role="tabpanel">';
                                $weather = [ 
                                    '&#9728;','&#127774;','&#9925;','&#127775;','&#127776;','&#127777;','&#127778;','&#127779;',
                                    '&#9928;','&#127786;','&#127787;','&#127788;','&#10052;','&#9731;','&#127777;','&#127752;',
                                    '&#9889;','&#127746;','&#9730;','&#128168;','&#127756;','&#127775;','&#127769;','&#127762;',
                                    '&#127761;','&#11088;','&#9732;','&#127765;','&#127766;','&#127767;','&#127768;','&#127763;',
                                    '&#127764;','&#127757;','&#127758;','&#127759;','&#129680;'
                                ];
                                
                                foreach ($weather as $weath) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($weath) . '\')">' . htmlspecialchars_decode($weath) . '</span>';
                                }
                        echo '</div>
                            <div class="tab-pane" id="symbols-' . $row['ann_id'] . '" role="tabpanel">';
                                $symbols = [ 
                                    '&#127881;','&#127882;','&#129395;','&#127880;','&#127874;','&#127873;','&#129665;','&#129681;',
                                    '&#127879;','&#127878;','&#129512;','&#10024;','&#127775;','&#128171;','&#127925;','&#127926;',
                                    '&#127908;','&#127911;','&#129668;','&#127942;','&#127935;','&#129351;','&#129352;','&#129353;',
                                    '&#10013;','&#9770;','&#9784;','&#9775;','&#10017;','&#128303;','&#128329;','&#128720;',
                                    '&#128334;','&#9774;','&#129418;','&#9851;','&#9884;','&#9888;','&#128696;','&#9940;',
                                    '&#128683;','&#10060;','&#10004;','&#128308;','&#128309;','&#9898;','&#9899;','&#128312;',
                                    '&#128311;'
                                ];
                                
                                foreach ($symbols as $sym) {
                                    echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($sym) . '\')">' . htmlspecialchars_decode($sym) . '</span>';
                                }
                        echo '</div>
                        </div>
                    </div>';
            // echo '<div class="m-1">';
            // $emojis = [ 
            //        '&#127872;', '&#127873;', '&#127874;', '&#127878;', '&#127879;', '&#127880;', '&#127881;', '&#127882;', 
            //        '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', 
            //        '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;',
            //        '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;','&#128400;'
            //    ];
               
            //    foreach ($emojis as $emoji) {
            //        echo '<span class="emoji" onclick="insertEmoji(\'Mycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($emoji) . '\')">' . htmlspecialchars_decode($emoji) . '</span>';
            //    }
            // echo '</div>';
            echo '</div>';

            // Add new comment input section
            echo '</div>'; // Close cardbox
            echo '</section>'; // Close profile-feed

            echo '<div class="modal fade" id="comment-Modal' . htmlspecialchars($row['ann_id']) . '" tabindex="-1" role="dialog" style="height:100vh;overflow:hidden;">
                    <div class="modal-dialog modal-lg" role="document"style="margin-top:0px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">'. htmlspecialchars($row['bi_empfname']) . ' '. htmlspecialchars($row['bi_emplname']).' Post</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa fa-times-circle"style="font-size:24px;"></i></span>
                                </button>
                            </div>
                            <div class="modal-body" id="post-modalbody" style="height:480px;overflow:auto;">';
                                // Cardbox Item
                                echo '<div class="cardbox-item">';
                                if(strpos($row['ann_content'], '<figure') !== false){

                                    $imagePattern = '/<img\s+[^>]*src=["\']([^"\']+)["\']/i';

                                    // Match image sources
                                    preg_match_all($imagePattern, $row['ann_content'], $imageMatches);
                                    $sources = $imageMatches[1];

                                    foreach ($sources as $imgv) {
                                        echo '<img class="img-fluid" style="max-height: 500px !important;cursor: pointer;" src="https://teamtngc.com/hris2/pages/announcement/' . htmlspecialchars($imgv) . '">';
                                    }

                                }else if(strpos($row['ann_content'], '<figure') !== false){
                                    echo str_replace('../announcement', 'https://teamtngc.com/hris2/pages/announcement', $row['ann_content']);
                                }else{
                                    echo '<img class="img-fluid" style="max-height: 500px !important;cursor: pointer;" src="https://teamtngc.com/hris2/pages/announcement/' . htmlspecialchars($row['ann_content']) . '">';
                                }
                                // echo "".$row['ann_content']."";
                                echo '</div>'; // Close cardbox-item
                                // Cardbox Base
                                echo '<div class="cardbox-base">';
                                echo '<ul>';
                                echo '<li>';
                                echo '<div class="reaction-container">';
                                $stmt = $port_db->prepare("
                                    SELECT reaction_type FROM tbl_reaction 
                                    WHERE reaction_by = ?
                                    AND post_id = ?
                                    GROUP BY reaction_type
                                ");
                                $stmt->execute([$user_id,$row['ann_id']]);
                                $ireact = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                // Check if the reaction type is 'heart'
                                if ($ireact && $ireact['reaction_type'] == 'heart') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'like') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'love') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'eey') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="https://i.pinimg.com/564x/cc/12/e0/cc12e02e7eed4491de74e05ea8a019a5.jpg" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'cry') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'haha') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'wow') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle">
                                          </a>';
                                }elseif ($ireact && $ireact['reaction_type'] == 'angry') {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <img src="/zen/assets/reactions/sadness.WEBP class="img-fluid rounded-circle">
                                          </a>';
                                } else {
                                    echo '<a id="react-button-' . htmlspecialchars($row['ann_id']) . '" class="reaction-trigger">
                                            <i class="ti-face-smile"></i>
                                          </a>';
                                }

                                 echo '<input type="hidden" name="post-id" value="' .htmlspecialchars($row['ann_id']). '" />
                                <div class="reaction-options">
                                    <div name="reaction" class="reaction" data-reaction="like"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/like.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="eey"><img width="50" height="60" src="https://i.pinimg.com/originals/58/91/52/58915204d17860c24d4c02be7425a830.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="heart"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/heart.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="love"><img class="img" width="50" height="50" src="https://media1.tenor.com/m/63nE7vC84pIAAAAd/care-discord.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="cry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/sad.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="haha"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/haha.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="wow"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/woow.gif"></div>
                                    <div name="reaction" class="reaction" data-reaction="angry"><img style="max-width: 40px;max-height:40px;" src="/zen/assets/reactions/angry.gif"></div>
                                </div>
                                </div>
                                </li>';
                                $stmt = $port_db->prepare("
                                    SELECT reaction_type 
                                    FROM tbl_reaction 
                                    WHERE post_id = ?
                                    AND reaction_by <> ?
                                    GROUP BY reaction_type
                                ");
                                $stmt->execute([$row['ann_id'], $user_id]);
                                $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($reactions as $react) {
                                    if ($react['reaction_type'] == 'like') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'heart') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'eey') {
                                        echo '<li><a href="#"><img src="https://i.pinimg.com/564x/9d/04/2c/9d042cb030e250961454adf7131f76b5.jpg" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'love') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/care.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'cry') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'haha') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'wow') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                    if ($react['reaction_type'] == 'angry') {
                                        echo '<li><a href="#"><img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle"></a></li>';
                                    }
                                }


                                if ($reactions !== false && isset($reactions['reaction_count'])) {
                                    echo '<li><a><span>' . htmlspecialchars($reactions['reaction_count']) . '</span></a></li>';
                                } else {
                                    echo '<li><a><span></span></a></li>';
                                }
                                echo '</ul>';
                                echo '<ul class="float-right">';
                                $stmt = $port_db->prepare("
                                    SELECT com_post_id, COUNT(*) AS comment_count 
                                    FROM tbl_post_comment 
                                    WHERE com_post_id = ?
                                    GROUP BY com_post_id
                                    
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $cm = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                $stmt = $hr_db->prepare("
                                    SELECT comment_onid, COUNT(*) AS counts 
                                    FROM tbl_comment 
                                    WHERE comment_onid = ?
                                    GROUP BY comment_onid
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $comcount = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                // Calculate total comments
                                $cmCount = $cm['comment_count'] ?? 0; // Use null coalescing for safer handling
                                $comcountCount = $comcount['counts'] ?? 0;
                                
                                $totalComments = $cmCount + $comcountCount;
                                
                                // Display total comments if greater than 0
                                if ($totalComments > 0) {
                                    echo '<li><a style="cursor: pointer;" data-toggle="modal" data-target="#comment-Modal' . htmlspecialchars($row['ann_id']) . '">
                                            <span>' . htmlspecialchars($totalComments) . ' <i class="ti-comment"></i></span>
                                          </a></li>';
                                } else {
                                    echo '<li><a><span>0 <i class="ti-comment"></i></span></a></li>'; // Show 0 comments for empty cases
                                }
                                echo '</ul>';
                                echo '</div>';
                                
                                // Fetch comments from both databases and merge them
                                $comments = [];
                                
                                $stmt = $port_db->prepare("
                                    SELECT 
                                      a.*, b.bi_empfname, b.bi_emplname, b.bi_empno
                                    FROM
                                      tbl_post_comment a
                                    LEFT JOIN tbl201_basicinfo b
                                    ON b.bi_empno = a.com_post_by
                                    WHERE a.com_post_id = ?
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
                                
                                $stmt = $port_db->prepare("
                                    SELECT 
                                      bi_empno,
                                      comment_onid AS com_post_id,
                                      comment_content AS com_content,
                                      comment_datetime AS com_date,
                                      bi_empno,
                                      bi_empfname,
                                      bi_emplname
                                    FROM tbl_comment
                                    LEFT JOIN tbl_announcement ON ann_id = comment_onid
                                    LEFT JOIN tbl_user2 ON U_ID = comment_by
                                    LEFT JOIN tbl201_basicinfo ON bi_empno = Emp_No
                                    WHERE comment_onid = ?
                                    GROUP BY bi_empno,comment_onid,comment_content
                                ");
                                $stmt->execute([$row['ann_id']]);
                                $comments = array_merge($comments, $stmt->fetchAll(PDO::FETCH_ASSOC));
                                
                                // Sort comments by date
                                usort($comments, function($a, $b) {
                                    return strtotime($a['com_date']) - strtotime($b['com_date']);
                                });
                                
                                // Loop through comments and display them
                                if (!empty($comments)) {
                                foreach ($comments as $c) {

                                    echo '<div class="cardbox-base-comment">';
                                    echo '<div class="media m-1">';
                                    echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
                                    echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($c['bi_empno']) . '.JPG" alt="User"></a>';
                                    echo '</div>';
                                    echo '<div class="media-body">';
                                    echo '<p class="m-0">' . htmlspecialchars($c['bi_empfname']) . ' ' . htmlspecialchars($c['bi_emplname']) . '</p>';
                                    echo '<small><span><i class="icon ion-md-pin"></i> ' . htmlspecialchars_decode($c['com_content']) . '</span></small>';

                                    try {
                                        // Set the timezone explicitly
                                        $timezone = new DateTimeZone('Asia/Manila'); // Adjust to your timezone
                                    
                                        $commentTime = new DateTime($c['com_date'], $timezone);
                                        $currentTime = new DateTime('now', $timezone); // Use the same timezone
                                    
                                        $interval = $commentTime->diff($currentTime);
                                    
                                        // Generate "time ago" string
                                        if ($interval->y > 0) {
                                            $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->m > 0) {
                                            $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->d > 0) {
                                            $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->h > 0) {
                                            $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                        } elseif ($interval->i > 0) {
                                            $timeAgo = $interval->i . ' min' . ($interval->i > 1 ? 's' : '') . ' ago';
                                        } else {
                                            $timeAgo = 'Just now';
                                        }
                                    } catch (Exception $e) {
                                        $timeAgo = 'Invalid date'; // Fallback in case of parsing issues
                                    }

                                    echo '<div class="comment-reply">
                                            <small><a>' . htmlspecialchars($timeAgo) . '</a></small>
                                          </div>';
                                    echo '</div>'; // Close media-body
                                    echo '</div>'; // Close media
                                    echo '</div>'; // Close cardbox-base-comment
                                    }
                                }
                                // <small><a style="cursor: pointer;">Reply</a></small>

                                echo '<div id="comment-section"></div>';

                         echo '</div>';
                         echo '<div class="modal-footer">';
                         // Add new comment input section
                         echo '<div class="cardbox-base-comment" id="hidden-div" style="width:100%;">';
                         echo '<div class="media m-1">';
                         echo '<div class="d-flex mr-1" style="margin-left: 20px;">';
                         echo '<a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/' . htmlspecialchars($user_id) . '.JPG" alt="User"></a>';
                         echo '</div>';
                         echo '<div class="media-body" id="comment">';
                         echo '<div class="textarea-wrapper">';
                         echo '<input type="hidden" name="com-id" value="' . htmlspecialchars($row['ann_id']) . '" />';
                         echo '<input type="text" name="SMycomment-' . htmlspecialchars($row['ann_id']) . '" placeholder="Write a comment..." id="SMycomment-' . htmlspecialchars($row['ann_id']) . '" class="emojiable-option"></input>';
                         echo '<i class="ti-face-smile icon emoji-icon" onclick="showEmoji(' . $row['ann_id'] . ')"></i>';
                         echo '</div>'; // Close textarea-wrapper
                         echo '<a href="#" id="saveComment-' . htmlspecialchars($row['ann_id']) . '" onclick="saveComment(' . htmlspecialchars($row['ann_id']) . '); return false;"><img src="assets/img/send_icon.png" height="30" width="30"/></a>';
                         echo '</div>'; // Close media-body
                         echo '</div>'; // Close media
                         echo '</div>'; // Close cardbox-base-comment
                         //Add new comment input section
                               
                        echo '</div>';
                        //footer end

                        echo '<div class="modal-footer">';
                         // Add new comment input section
                         echo '<div class="cardbox-base-comment" id="emoji-div-' . $row['ann_id'] . '" style="width:100%; display:none;">';
                         echo '<div class="m-1">';
                         $emojis = [
                                '&#128512;', '&#128513;', '&#128514;', '&#128515;', '&#128516;', '&#128517;', '&#128518;', '&#128519;',
                   
                                '&#129297;', '&#129303;', '&#129312;', '&#129319;', '&#129321;', '&#129395;', '&#129392;', '&#129327;',
                                '&#128520;', '&#128521;', '&#128522;', '&#128523;', '&#128524;', '&#128525;', '&#128526;', '&#128527;',
                                '&#128528;', '&#128529;', '&#128530;', '&#128531;', '&#128532;', '&#128533;', '&#128534;', '&#128535;',
                                '&#128536;', '&#128537;', '&#128538;', '&#128539;', '&#128540;', '&#128541;', '&#128542;', '&#128543;',
                                '&#128544;', '&#128545;', '&#128546;', '&#128547;', '&#128548;', '&#128549;', '&#128550;', '&#128551;',
                                '&#128552;', '&#128553;', '&#128554;', '&#128555;', '&#128556;', '&#128557;', '&#128558;', '&#128559;',
                                '&#128560;', '&#128561;', '&#128562;', '&#128563;', '&#128564;', '&#128565;', '&#128566;', '&#128567;', 
                                '&#127872;', '&#127873;', '&#127874;', '&#127878;', '&#127879;', '&#127880;', '&#127881;', '&#127882;', 
                                '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', 
                                '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;',
                                '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;','&#128400;'
                            ];
                            
                            foreach ($emojis as $emoji) {
                                echo '<span class="emoji" onclick="insertEmoji(\'SMycomment-' . htmlspecialchars($row['ann_id']) . '\', \'' . htmlspecialchars($emoji) . '\')">' . $emoji . '</span>';

                            }
                         echo '</div>';
                         echo '</div>';      
                        echo '</div>';
                        //footer end

                     echo '</div>';
                echo '</div>';
            echo '</div>';
    }
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>



<script>
    $(document).ready(function () {
    // Event delegation for reaction triggers
    $(document).on('click', '.reaction-trigger', function () {
        $(this).siblings('.reaction-options').toggle();
    });

    // Event delegation for reactions
    $(document).on('click', '.reaction', function () {
        var reactionType = $(this).data('reaction');
        var postBy = $(this).data('reacted_by');
        var postId = $(this).closest('.reaction-container').find('.reaction-trigger').attr('id').split('-')[2];
        var $reactionTrigger = $(this).closest('.reaction-container').find('.reaction-trigger');

        $.ajax({
            url: 'reaction',
            method: 'POST',
            data: { post_id: postId, reaction: reactionType, reacted_by: postBy },
            success: function (response) {
                $(this).closest('.reaction-container').find('.reaction-options').hide();
                $reactionTrigger.hide();

                var reactionImage;
                switch (reactionType) {
                    case 'like':
                        reactionImage = '<img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle" alt="Like">';
                        break;
                    case 'heart':
                        reactionImage = '<img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle" alt="Heart">';
                        break;
                    case 'love':
                        reactionImage = '<img src="https://i.pinimg.com/564x/1e/b9/ab/1eb9abce88c9859c08e70330ef8495dc.jpg" class="img-fluid rounded-circle" alt="Love">';
                        break;
                    case 'cry':
                        reactionImage = '<img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle" alt="Cry">';
                        break;
                    case 'haha':
                        reactionImage = '<img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle" alt="Haha">';
                        break;
                    case 'wow':
                        reactionImage = '<img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle" alt="Money">';
                        break;
                    case 'angry':
                        reactionImage = '<img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle" alt="Angry">';
                        break;
                    case 'eey':
                        reactionImage = '<img src="https://i.pinimg.com/564x/cc/12/e0/cc12e02e7eed4491de74e05ea8a019a5.jpg" class="img-fluid rounded-circle" alt="Eey">';
                        break;
                    default:
                        reactionImage = '';
                }

                $reactionTrigger.html(reactionImage).show();
            }.bind(this),
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

$(document).ready(function(){
    let mentionList = $("#mention-list");
    let textarea = $("#post-desc");

    textarea.on("input", function() {
        let cursorPos = this.selectionStart;
        let text = $(this).val().substring(0, cursorPos);
        let match = text.match(/@([\w]*)$/);

        if (match) {
            let searchQuery = match[1];

            if (searchQuery.length > 0) {
                $.ajax({
                    url: "persons",
                    method: "POST",
                    data: { query: searchQuery },
                    success: function(response) {
                        mentionList.html(response).show();
                    }
                });
            } else {
                mentionList.hide();
            }
        } else {
            mentionList.hide();
        }
    });

    $(document).on("click", ".mention-item", function() {
        let name = $(this).text();
        let text = textarea.val();
        let cursorPos = textarea[0].selectionStart;
        let textBefore = text.substring(0, cursorPos).replace(/@[\w]*$/, "@" + name + " ");
        let textAfter = text.substring(cursorPos);

        textarea.val(textBefore + textAfter).focus();
        mentionList.hide();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest(".textarea-wrapper").length) {
            mentionList.hide();
        }
    });
});

$(document).ready(function(){
    let mentionList = $("#mention-list");
    let textarea = $("#post-desc2");

    textarea.on("input", function() {
        let cursorPos = this.selectionStart;
        let text = $(this).val().substring(0, cursorPos);
        let match = text.match(/@([\w]*)$/);

        if (match) {
            let searchQuery = match[1];

            if (searchQuery.length > 0) {
                $.ajax({
                    url: "persons",
                    method: "POST",
                    data: { query: searchQuery },
                    success: function(response) {
                        mentionList.html(response).show();
                    }
                });
            } else {
                mentionList.hide();
            }
        } else {
            mentionList.hide();
        }
    });

    $(document).on("click", ".mention-item", function() {
        let name = $(this).text();
        let text = textarea.val();
        let cursorPos = textarea[0].selectionStart;
        let textBefore = text.substring(0, cursorPos).replace(/@[\w]*$/, "@" + name + " ");
        let textAfter = text.substring(cursorPos);

        textarea.val(textBefore + textAfter).focus();
        mentionList.hide();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest(".textarea-wrapper").length) {
            mentionList.hide();
        }
    });
});

$(document).ready(function(){
    $(document).on("input", "input[name^='Mycomm-']", function() {
        let textarea = $(this);
        let annId = textarea.attr("id").replace("Mycomment-", "");
        let mentionList = $("#mention-list-" + annId);

        let cursorPos = this.selectionStart;
        let text = textarea.val().substring(0, cursorPos);
        let match = text.match(/@([\w]*)$/);

        if (match) {
            let searchQuery = match[1];

            if (searchQuery.length > 0) {
                $.ajax({
                    url: "persons",
                    method: "POST",
                    data: { query: searchQuery },
                    success: function(response) {
                        mentionList.html(response).show();
                    }
                });
            } else {
                mentionList.hide();
            }
        } else {
            mentionList.hide();
        }
    });

    $(document).on("click", ".mention-item", function() {
        let name = $(this).text();
        let textarea = $(this).closest(".textarea-wrapper").find("input[name^='Mycomm-']");
        let cursorPos = textarea[0].selectionStart;
        let text = textarea.val();
        let textBefore = text.substring(0, cursorPos).replace(/@[\w]*$/, "@" + name + " ");
        let textAfter = text.substring(cursorPos);

        textarea.val(textBefore + textAfter).focus();
        $(this).parent().hide();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest(".textarea-wrapper").length) {
            $(".mention-list").hide();
        }
    });
});

$('#post-btn').click(function (e) {
    e.preventDefault();

    var postedBy = $('input[name="posted-by"]').val();
    var postDesc = $('#post-desc').val();
    var audience = $('input[name="audience"]:checked').val();
    var postContent = new FormData();

    postContent.append('postedBy', postedBy);
    postContent.append('postDesc', postDesc);
    postContent.append('audience', audience);

    var files = $('#imageInput')[0].files;
    for (var i = 0; i < files.length; i++) {
        postContent.append('postsimg[]', files[i]);
    }

    $.ajax({
        url: 'postnews',
        type: 'POST',
        data: postContent,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            console.log('Server response:', response);
            if (response.success) {
                $('#default-Modal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});



// $('#post-btn').click(function (e) {
//     e.preventDefault();

//     var postedBy = $('input[name="posted-by"]').val();
//     var postDesc = $('#post-desc').val();
//     var audience = $('input[name="audience"]:checked').val();
//     var postContent = new FormData();

//     postContent.append('postedBy', postedBy);
//     postContent.append('postDesc', postDesc);
//     postContent.append('audience', audience);

//     var fileInput = $('#imageInput')[0].files[0];
//     if (fileInput) {
//         postContent.append('file', fileInput);
//     }

//     $.ajax({
//         url: 'postnews',
//         type: 'POST',
//         data: postContent,
//         processData: false,
//         contentType: false,
//         dataType: 'json',
//         success: function (response) {
//             console.log('Server response:', response); // Debugging
//             if (response.success) {
//                 $('#default-Modal').modal('hide');
//                 setTimeout(() => {
//                     location.reload();
//                 }, 500); // Small delay to ensure modal hides smoothly
//             } else {
//                 alert('Error: ' + response.error);
//             }
//         },
//         error: function (xhr, status, error) {
//             console.error('AJAX error:', error);
//         }
//     });
// });

    
    $('#post-desc').on('input', function () {
        if ($(this).val().trim() !== '') {
            $('.post-btn').prop('disabled', false);
        } else {
            $('.post-btn').prop('disabled', true);
        }
    });
    $('#post-desc2').on('input', function () {
        if ($(this).val().trim() !== '') {
            $('.post-btn').prop('disabled', false);
        } else {
            $('.post-btn').prop('disabled', true);
        }
    });
});

// Save comment function
function saveComment(postId) {
    const commentInput = document.getElementById(`Mycomment-${postId}`);
    const comIdInput = document.querySelector(`input[name="com-id"][value="${postId}"]`);

    if (!commentInput || !comIdInput) {
        alert('Unable to find input fields for this post.');
        return;
    }

    const comment = commentInput.value.trim();
    const comId = comIdInput.value;

    if (comment === '') {
        alert('Comment cannot be empty!');
        return;
    }

    fetch('save_comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `com_id=${encodeURIComponent(comId)}&Mycomment=${encodeURIComponent(comment)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                commentInput.value = '';
                reloadComments(postId);
            } else {
                alert(data.message || 'An error occurred while saving the comment.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}

// Reload comments
function reloadComments(postId) {
    $.ajax({
        url: 'comment',
        type: 'POST',
        data: { post_id: postId },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                const comment = response.comment;
                const newCommentHTML = `
                    <div class="cardbox-base-comment">
                        <div class="media m-1">
                            <div class="d-flex mr-1" style="margin-left: 20px;">
                                <a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/${comment.bi_empno}.JPG" alt="User"></a>
                            </div>
                            <div class="media-body">
                                <p class="m-0">${comment.bi_empfname} ${comment.bi_emplname}</p>
                                <small><span><i class="icon ion-md-pin"></i> ${comment.com_content}</span></small>
                                <div class="comment-reply">
                                    <small><a href="#">Just now</a></small>
                                    <small><a style="cursor: pointer;">Reply</a></small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $(`#prof-${postId} #comment-section`).append(newCommentHTML);
            } else {
                console.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching the new comment:', error);
        }
    });
}
let page = 1;

    function loadMorePosts() {
        $('#loading').show();
        $.ajax({
            url: 'post',
            type: 'POST',
            data: { page: page },
            success: function(response) {
                $('#loading').hide();
                if (response.trim() !== '') {
                    $('#myDiv').append(response);
                    page++;
                } else {
                    $('#loading').html('No more posts available.');
                }
            }
        });
    }

    // Initial load
    $(document).ready(function() {
        loadMorePosts();

        // Detect when the user reaches the bottom of the page
        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height()) {
                loadMorePosts();
            }
        });
    });

    // Get elements
    const firstPick = document.getElementById('first-pick');
    const secondPick = document.getElementById('second-pick');
    const firstPicker = document.getElementById('first-picker');
    const secondPicker = document.getElementById('second-picker');
    const textpost = document.getElementById('post-desc');
    const addTextPost = document.getElementById('add-text-post');
    const image = document.getElementById('img-back');
    const back = document.getElementById('back-picker');
    const post = document.getElementById('post-btn');
    const post2 = document.getElementById('post-btn2');
    const textArea = document.getElementById('post-desc2');

    firstPicker.addEventListener('click', () => {
        firstPick.style.display = 'none';
        secondPick.style.display = 'flex';
    });

    secondPicker.addEventListener('click', () => {
        secondPick.style.display = 'none';
        firstPick.style.display = 'flex';
    });

    image.addEventListener('click', () => {
        textpost.style.display = 'none';
        addTextPost.style.display = 'block';
        post.style.display = 'none';
        post2.style.display = 'block';
    });

    back.addEventListener('click', () => {
        textpost.style.display = 'block';
        addTextPost.style.display = 'none';
        post.style.display = 'block';
        post2.style.display = 'none';
    });

    // Set background image selection
    document.querySelectorAll('#second-pick .background-picker img').forEach(image => {
        image.addEventListener('click', () => {
            const selectedBackground = image.getAttribute('data-bg');
            if (selectedBackground) {
                addTextPost.style.backgroundImage = `url('${selectedBackground}')`;
                addTextPost.style.backgroundSize = 'cover';
                addTextPost.style.backgroundPosition = 'center';
            }
        });
    });

document.getElementById('post-btn2').addEventListener('click', (event) => {
    event.preventDefault();

    const selectedAudience = document.querySelector('input[name="audience"]:checked');
    const audienceValue = selectedAudience ? selectedAudience.value : null;

    if (!audienceValue) {
        alert('Please select an audience before posting.');
        return;
    }

    // Ensure fonts are loaded before rendering
    document.fonts.ready.then(() => {
        const dpi = window.devicePixelRatio || 1;
        html2canvas(addTextPost, {
            scale: 10, // Increase scale for better resolution
            useCORS: true, // Handle cross-origin resources
            logging: true, // Enable debug logs
        })
        .then(canvas => {
            const imageData = canvas.toDataURL('image/png');
            // const imageData = canvas.toDataURL('image/png', 1.0);
            if (!imageData) {
                alert('Failed to generate image data.');
                return;
            }

            const content = textArea.value;

            fetch('save_post', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    image: imageData,
                    content: content,
                    audience: audienceValue,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // alert('Post saved successfully!');
                    location.reload();
                } else {
                    alert('Failed to save the post.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        })
        .catch(error => {
            console.error('Error capturing canvas:', error);
        });
    });
});
const showIcon = document.getElementById('showIcon');
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');

    // Open file input when the fixed div is clicked
    showIcon.addEventListener('click', () => {
      imageInput.click();
    });

    // Handle file selection and preview
    imageInput.addEventListener('change', function () {
      const files = Array.from(this.files);

      // Clear previous previews
      previewContainer.innerHTML = '';

      if (files.length > 0) {
        // Show the preview container if images are selected
        previewContainer.style.display = 'flex';

        // Determine if it's single or multiple images
        if (files.length === 1) {
          previewContainer.className = 'add-photos-video'; // Single image
        } else {
          previewContainer.className = 'add-photos-video multiple'; // Grid for multiple images
        }

        // Preview images
        files.forEach((file) => {
          const reader = new FileReader();
          reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'image-preview';
            previewContainer.appendChild(img);
          };
          reader.readAsDataURL(file);
        });
      } else {
      }

    });
function showDiv(uniqueId) {
    const hiddenDiv = document.getElementById('hidden-div-' + uniqueId);
    if (hiddenDiv) {
        hiddenDiv.style.display = hiddenDiv.style.display === 'none' ? 'block' : 'none';
    }
}
function showEmoji(emojiId) {
    const emojiDiv = document.getElementById('emoji-div-' + emojiId);
    if (emojiDiv) {
        emojiDiv.style.display = emojiDiv.style.display === 'none' ? 'block' : 'none';
    }
}
function insertEmoji(inputId, emojiCode) {
    const inputField = document.getElementById(inputId);

    // Decode the emoji code to get the actual emoji
    const tempElement = document.createElement('span');
    tempElement.innerHTML = emojiCode;
    const emoji = tempElement.textContent || tempElement.innerText;

    if (inputField) {
        inputField.value += emoji; // Append the actual emoji to the input field
    } else {
        console.error('Input field not found:', inputId);
    }
}
document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.emoji-tab-btn');
  const panels = document.querySelectorAll('.emoji-tab-panel');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      // Remove active class from all tabs and panels
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));

      // Add active class to the clicked tab and corresponding panel
      tab.classList.add('active');
      const panelId = tab.getAttribute('data-tab');
      document.getElementById(panelId).classList.add('active');
    });
  });
});

$(document).on('click', '.report-btn', function () {
    const modal = $(this).closest('.modal');
    const postID = $(this).data('postid');
    const reason = modal.find('textarea[name="reason"]').val();

    const formData = {
        postID: postID,
        Reasons: reason
    };

    $.ajax({
        url: 'report_post',
        method: 'POST',
        data: formData,
        success: function (res) {
            // alert("Post reported successfully!");
            $('.modal').modal('hide');
            location.reload();
        },
        error: function () {
            alert("Error saving the form.");
        }
    });
});
</script>