<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<?php
require_once($sr_root."/actions/get_profile.php");
$pic = Profile::GetProfile($empno);
?>
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
                        <div class="profile">
                          <div class="profile-container">
                                <?php if (!empty($pic)) { foreach ($pic as $p) { ?>
                                <img src="/zen/assets/profile_picture/<?=$p['prof_image']?>" alt="Profile" class="profile-img">
                                <?php } }else{ ?>
                                <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="Profile" class="profile-img">
                                <?php } ?>
                                <!-- <div class="camera-icon">
                                    <label for="file-input">📷</label>
                                    <input type="file" id="file-input" accept="image/*">
                                </div> -->
                            </div>
                          <!-- <img src="https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/e-class/Thumbnail/img/<?= $empno ?>.JPG" alt="User-Profile-Image" width="100" height="100" style="border-radius: 50px;"> -->
                          <div class="basic-info">
                            <span id="userName">
                            <?php
                                echo $username;
                            ?>
                            </span>
                            <p><?php
                                echo $position;
                            ?></p>
                            <p><?php
                                echo $empno;
                            ?></p>
                          </div>
                        </div>
                        <div class="edit-profile">
                          <button class="btn btn-default btn-mini" data-toggle="modal" data-target="#Enneagram"> Take Enneagram Test</button>
                        </div> 
                      </div>
                    </div>
                    <div class="card" id="enne-card">
                        <div class="card-block" id="inne">
                         
                        </div>
                        <div class="card-block">
                                <!-- <h6 class="sub-title">Tab With Icon</h6> -->
                                <!-- <div class="sub-title">Tab With Icon</div>                                         -->
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs tabs " role="tablist" style="font-size: 9px !important;">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#perfect" role="tab" style="color: #ba5e5b !important;font-size: 11px;"><i class="icofont icofont-law-alt-2"></i>(1) PERFECTIONIST</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#helper" role="tab" style="color: #c48660 !important;font-size: 11px;"><i class="icofont icofont-ui-user "></i>(2) HELPER</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#achive" role="tab" style="color: #c9c45f !important;font-size: 11px;"><i class="icofont icofont-trophy-alt"></i>(3) ACHIEVER</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#romantic" role="tab" style="color: #65c985 !important;font-size: 11px;"><i class="icofont icofont-ui-love"></i>(4) ROMANTIC</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#observer" role="tab" style="color: #5ec1c4 !important;font-size: 11px;"><i class="icofont icofont-eye-alt"></i>(5) OBSERVER</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#questioner" role="tab" style="color: #685cb8 !important;font-size: 11px;"><i class="icofont icofont-question-circle"></i>(6) QUESTIONER</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#adventurer" role="tab" style="color: #a14f9a !important;font-size: 11px;"><i class="icofont icofont-travelling"></i>(7) ADVENTURER</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#asserter" role="tab" style="color: #a65162 !important;font-size: 11px;"><i class="icofont icofont-check-circled"></i>(8) ASSERTER</a>
                                        
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#peacemaker" role="tab" style="color: #122387 !important;font-size: 11px;"><i class="icofont icofont-holding-hands"></i>(9) PEACEMAKER</a>
                                        
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content tabs card-block">
                                    <div class="tab-pane active" id="perfect" role="tabpanel">
                                        <p class="m-1">PERFECTIONIST</p>
                                        <h6>Ones are motivated by the need to live their life the right way, including improving themselves and the world around them.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Ones at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Ethical</td>
                                                            <td>Reliable</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Productive</td>
                                                            <td>Wise</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Idealistic</td>
                                                            <td>Fair</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Honest</td>
                                                            <td>Orderly</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Self-disciplined</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Ones at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Judgmental</td>
                                                            <td>Inflexible</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Dogmatic (strict)</td>
                                                            <td>Obsessive-compulsive</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Critical of others</td>
                                                            <td>Overly Serious</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Controlling</td>
                                                            <td>Anxious</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Jealous</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Take your share of the responsibility so I don’t end up with all the work.</p>
                                                        <p>Acknowledge my achievements.</p>
                                                        <p>I’m hard on myself. Reassure me that I’m fine the way I am.</p>
                                                        <p>Tell me that you value my advice.</p>
                                                        <p>Be fair and considerate, as I am.</p>
                                                        <p>Apologize if you have been unthoughtful. It will help me to forgive.</p>
                                                        <p>Gently encourage me to lighten up and to laugh at myself when I get uptight, but hear my worries first.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Ones at their best in a relationship are loyal, dedicated, conscientious, and helpful. They are well balanced and have a good sense of humor.</p>
                                                        <p>Ones at their worst in a relationship are critical, argumentative, nit-picking, and uncompromising. They have high expectations of others.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Ones are efficient, organized, and always complete the task. The more analytical and tough-minded Ones are found in management, science, and law enforcement. The more people-oriented Ones are found in health care, education, and religious work. </p>
                                                        
                                                        <p>Since they do things in a professional, honest, and ethical manner, you would do well to have Ones as your car mechanic, surgeon, dentist, banker, and stockbroker.
                                                        </p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="helper" role="tabpanel">
                                        <p class="m-1">HELPER</p>
                                        <h6>Two are motivated by the need to be loved and valued and to express their positive feelings toward others. Traditionally society has encouraged Two qualities in females more than in males.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Twos at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Loving</td>
                                                            <td>Caring</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Adaptable</td>
                                                            <td>Insightful</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Generous</td>
                                                            <td>Enthusiastic</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Turned in to how</td>
                                                            <td>People feel</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Twos at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Martyr like</td>
                                                            <td>Indirect</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Manipulative</td>
                                                            <td>Possessive</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Hysterical</td>
                                                            <td>Overly Accommodating</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Overly demonstrative (the more extroverted Twos)</td>
                                                           
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Tell me that you appreciate me. Be specific.</p>
                                                        <p>Share fun times with me.</p>
                                                        <p>Take an interest in my problems, though I will probably try to focus on yours.</p>
                                                        <p>Let me know that I am important and special to you.</p>
                                                        <p>Be gentle if you decide to criticize me.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Twos at their best in a relationship are attentive, appreciative, generous, warm, playful, and nurturing.</p>
                                                        <p>Twos makes their partners feel special and loved.</p>
                                                        <p>Twos at their worst in relationship are controlling, possessive, needy, and insincere. Since they have trouble asking directly, they tend to manipulate to get what they want.</p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Twos usually prefer to work with people, often in the helping professions, as counselors, teachers, and health workers. </p>
                                                        <p>Extroverted twos are sometimes found in the limelight as actresses, actors, and motivational speakers.</p>
                                                        </p>
                                                        <p>Twos also work in sales and helping others as receptionists, secretaries, assistants, decorators, and clothing consultants.</p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="achive" role="tabpanel">
                                        <p class="m-1">ACHIEVER</p>
                                        <h6>Threes are motivated by the need to be productive, achieve success, and avoid failure.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Threes at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Optimistic</td>
                                                            <td>Confident</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Industrious</td>
                                                            <td>Efficient</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Self-propelled</td>
                                                            <td>Energetic</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Practical</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Threes at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Deceptive</td>
                                                            <td>Narcissistic</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Pretentious</td>
                                                            <td>Vain</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Superficial</td>
                                                            <td>Vindictive</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Overly competitive</td>
                                                           
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Leave me alone when I am doing my work.</p>
                                                        <p>Give me honest, but not unduly critical or judgmental, feedback.</p>
                                                        <p>Help me keep my environment harmonious and peaceful.</p>
                                                        <p>Don’t burden me with negative emotions. </p>
                                                        <p>Tell me when you’re proud of me or my accomplishments.</p>
                                                        <p>Tell me you like being around me.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Threes at their best in relationship value and accept their partners. They are playful, giving, responsible, and well regarded by others in the community.</p>
                                                        <p>Threes are their worst in a relationship are preoccupied with work and projects. They are self-absorbed, defensive, impatient, dishonest, and controlling.</p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>These are hardworking, goal oriented, organized, and decisive. They are frequently in management or leadership positions in business, law, banking, the computer field, and politics. Being in the public eye, as broadcasters and performers, is also common. The more helping-oriented Threes also become homemakers who put tremendous energy into their responsibilities.</p> 
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="romantic" role="tabpanel">
                                        <p class="m-1">ROMANTIC</p>
                                        <h6>Fours are motivated by the need to experience their feeling and to be understood, to search for the meaning of life, and to avoid being ordinary.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Fours at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Warm</td>
                                                            <td>Compassionate</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Introspective</td>
                                                            <td>Expressive</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Creative</td>
                                                            <td>Intuitive</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Supportive</td>
                                                            <td>Refined</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Fours at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Depressed</td>
                                                            <td>Self-conscious</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Guilt-ridden</td>
                                                            <td>Moralistic</td>
                                                            
                                                        </tr>
                                                        <tr>
                                                            <td>Withdrawn</td>
                                                            <td>Stubborn</td>
                                                           
                                                        </tr>
                                                        <tr>
                                                            <td>Moody</td>
                                                            <td>Self-absorbed</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Give me plenty of compliments. They mean a lot to me.</p>
                                                        <p>Be a supportive friend or partner. Help me to learn to love and value myself.</p>
                                                        <p>Respect me for my special gifts of intuition and vision.</p>
                                                        <p>Though I don’t always want to be cheered up when I’m feeling melancholy, I sometimes like to have someone lighten me up a little.</p>
                                                        <p>Don’t tell me I’m too sensitive or that I’m overreacting!</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Fours at their best in a relationship are empathic, supportive, gentle, playful, passionate, and witty. They are self-revealing and bond easily.</p>
                                                        <p>Fours at their worst in a relationship are too self-absorbed, jealous, emotionally needy, moody, self-righteous, and overly critical. They become hurt and feel rejected easily.</p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Fours can inspire, influence, and persuade through the arts (music, fine art, dancing) and the written or spoken word (poetry, novels, journalism, teaching). Many like to help bring out the best in people as psychologist or counselors. Some take pride in the small business they own. Often Fours accept mundane jobs to support their creative pursuits.</p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="observer" role="tabpanel">
                                        <p class="m-1">OBSERVER</p>
                                        <h6>Fives are motivated by the need to know and understand everything, to be self-sufficient, and to avoid looking foolish.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Fives at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Analytical</td>
                                                            <td>Persevering</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Sensitive</td>
                                                            <td>Wise</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Objective</td>
                                                            <td>Perceptive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Self-contained</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Fives at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Intellectually Arrogant</td>
                                                            <td>Stingy</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Stubborn</td>
                                                            <td>Distant</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Critical of others</td>
                                                            <td>Unassertive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Negative</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Be independent, not clingy.</p>
                                                        <p>Speak in a straightforward and brief manner.</p>
                                                        <p>I need time alone to process my feelings and thoughts. </p>
                                                        <p>Remember that if I seem aloof, distant, or arrogant, it may be that I am feeling uncomfortable.</p>
                                                        <p>Make me feel welcome, but not to intensely, or I might doubt your sincerity.</p>
                                                        <p>If I become irritated when I have to repeat things, it may be because it was such an effort to get my thoughts out in the first place.</p>
                                                        <p>Don’t come on like a bulldozer.</p>
                                                        <p>Help me to avoid my pet peeves: big parties, other people’s loud music, overdone emotions, and intrusions on my privacy.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Fives at their best in a relationship are kind, perceptive, open-minded, self-sufficient, and trustworthy.
                                                        Fives at their worst in a relationship are contentious, suspicious, withdrawn, and      negative. They are on their guard against being engulfed.
                                                        </p>
                                                        <p>Fives at their best in a relationship are kind, perceptive, open-minded, self-sufficient, and trustworthy.
                                                        Fives at their worst in a relationship are contentious, suspicious, withdrawn, and      negative. They are on their guard against being engulfed.
                                                        </p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Fives are often in scientific, technical, or other intellectually demanding fields. They have strong analytical skills and are good at problem solving. Those with a well-developed Four wing are more likely to be counselors, musicians, artists, or writers. Fives usually like to work alone and are independent thinkers.</p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="questioner" role="tabpanel">
                                        <p class="m-1">QUESTIONER</p>
                                        <h6>Sixes are motivated by the need for security. Phobic Sixes are outwardly fearful and seek approval. Counterphobic Sixes confront their fear. Both of these aspects can appear in the same person.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Sixes at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Loyal</td>
                                                            <td>Likable</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Caring</td>
                                                            <td>Warm</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Compassionate</td>
                                                            <td>Witty</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Practical</td>
                                                            <td>Helpful</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Responsible</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Sixes at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Hypervigilant</td>
                                                            <td>Controlling</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Unpredictable</td>
                                                            <td>Judgmental</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Paranoid</td>
                                                            <td>Defensive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Rigid</td>
                                                            <td>Self-defeating</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Testy</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Be direct and clear.</p>
                                                        <p>Listen to me carefully.</p>
                                                        <p>Don’ts judge me for my anxiety.</p>
                                                        <p>Work things through with me.</p>
                                                        <p>Reassure me that everything is OK between us.</p>
                                                        <p>Laugh and make jokes with me.</p>
                                                        <p>Gently push me toward new experiences.</p>
                                                        <p>Try not to overreact to my overreacting.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Sixes at their best in a relationship are warm, playful, open, loyal, supportive, honest, fair, and reliable.</p>
                                                        <p>Sixes at their worst in a relationship are suspicious, controlling, inflexible, and sarcastic. They either withdraw or put on a tough act when threatened.</p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Though sixes can be found in almost any career, they are often attracted to the justice system, the military, the corporate world, and academia. Sixes often like being part of a team. Many are in health care and education.
                                                        <br>
                                                        Counterphobic Sixes sometimes have jobs that involve risk. Those who learn toward the antiauthoritarian side are usually happier when self-employed.
                                                        <br>
                                                        If sixes are unhappy with their work situation, they are likely to become rebellious or secretive.
                                                        </p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="adventurer" role="tabpanel">
                                        <p class="m-1">ADVENTURER</p>
                                        <h6>Sevens are motivated by the need to be happy and plan enjoyable activities, to contribute to the world, and to avoid suffering and pain.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Sevens at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Fun-loving</td>
                                                            <td>Spontaneous</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Imaginative</td>
                                                            <td>Productive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Enthusiastic</td>
                                                            <td>Quick</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Confident</td>
                                                            <td>Charming</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Curious</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Sevens at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Narcissistic</td>
                                                            <td>Impulsive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Unfocused</td>
                                                            <td>Rebellious</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Undisciplined</td>
                                                            <td>Possessive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Manic</td>
                                                            <td>Self-destructive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Restless</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Give me companionship, affection, and freedom.</p>
                                                        <p>Engage with me in stimulating conversation and laughter.</p>
                                                        <p>Appreciate my grand visions and listen to my stories. </p>
                                                        <p>Don’t try to change my style. Accept me the way I am.</p>
                                                        <p>Be responsible for yourself. I dislike clingy or needy people. </p>
                                                        <p>Don’t tell me what to do.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Sevens at their best in a relationship are lighthearted, generous, outgoing, caring, and fun. They introduce their friends and loved ones to new activities and adventures.</p>
                                                        <p>Sevens at their worst in a relationship are narcissistic, opinionated, defensive, and distracted. They are often ambivalent about being tied down to a relationship.</p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Many sevens have several careers at once or jobs where they travel a lot (as pilots, flight attendants, or photographers, for example). Some like using tools or machines or working outdoors. Others prefer solving problems as entrepreneurs or troubleshooters. Still others are in the helping professions as teachers, nurses, or counselor. Sevens are not likely to be found in repetitive work (in assembly lines or accounting, for instance). They like challenges and think quickly in emergencies.</p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="asserter" role="tabpanel">
                                        <p class="m-1">ASSERTER</p>
                                        <h6>Eights are motivated by the need to be self-reliant and strong and to avoid feeling weak or dependent.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Eights at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Direct</td>
                                                            <td>Authoritative</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Loyal</td>
                                                            <td>Energetic</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Earthy</td>
                                                            <td>Protective</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Self-confident</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Eights at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Controlling</td>
                                                            <td>Rebellious</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Insensitive</td>
                                                            <td>Domineering</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Self-centered</td>
                                                            <td>Skeptical</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Aggressive</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>Stand up for yourself… and me.</p>
                                                        <p>Be confident, strong, and direct.</p>
                                                        <p>Don’t gossip about me or betray my trust.</p>
                                                        <p>Be vulnerable and share your feelings. See and knowledge my tender, vulnerable side.</p>
                                                        <p>Give me space to be alone.</p>
                                                        <p>Acknowledge the contributions I make, but don’t flatter me.</p>
                                                        <p>I often speak in an assertive way. Don’t automatically assume it’s a personal attack.</p>
                                                        <p>When I scream, curse, and stomp around, try to remember that’s just the way I am.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Eights at their best in a relationship are loyal, caring positive, playful, truthful, straightforward, committed, generous, and supportive.</p>
                                                        <p>Eights at their worst in a relationship are demanding, arrogant, combative, possessive, uncompromising, and quick to find fault.</p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Eights are good at taking the initiative to move ahead. They want to be in charge. Since they want the freedom to make their own choices, they are often self-employed. Eights have a strong need for financial security. Many are entrepreneurs, business executive, lawyers, military and union leaders, and sports figures. They are also in teaching and the helping and health professions. Eights are attracted to careers in which they can demonstrate their willingness to accept responsibility and take on and resolve difficult problems.</p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="peacemaker" role="tabpanel">
                                        <p class="m-1">PEACEMAKER</p>
                                        <h6>Nines are motivated by the need to keep the peace, to merge with others, and to avoid conflict. Since they especially, take on qualities of the other eight types, Nines have many variations in their personalities, from gentle and mild-mannered to independent and forceful.</h6><br>
                                        <div class="perf-container">
                                            <div class="first">
                                                <span>Nines at their BEST</span>
                                                <table class="table">
                                                    <tbody>
                                                        <tr>
                                                            <td>Pleasant</td>
                                                            <td>Peaceful</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Generous</td>
                                                            <td>Patient</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Receptive</td>
                                                            <td>Diplomatic</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Open-minded</td>
                                                            <td>Emphatic</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="second">
                                                <span>Nines at their WORST</span>
                                                <table class="table">
                                                    <tbody>
                                                      <tbody>
                                                        <tr>
                                                            <td>Spaced-out</td>
                                                            <td>Forgetful</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Stubborn</td>
                                                            <td>Obsessive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Apathetic</td>
                                                            <td>Passive-aggressive</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Judgmental</td>
                                                            <td>Unassertive</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="">
                                            <ul class="sticky">
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>HOW TO GET ALONG WITH ME</h5><br><br>
                                                        <p>If you want me to do something, how you ask is important. I especially don’t like expectations or pressure.</p>
                                                        <p>I like to listen and to be of service, but don’t take advantage of this.</p>
                                                        <p>Listen until I finish speaking, even though I meander (wander) a bit.</p>
                                                        <p>Give me time to finish things and make decisions. It’s OK to nudge me gently and nonjudgmentally.</p>
                                                        <p>Ask me questions to help me get clear.</p>
                                                        <p>Tell me when you like how I look. I’m not averse (reluctant) to flattery.</p>
                                                        <p>Hug me, show physical affection. It opens me up to my feelings.</p>
                                                        <p>I like a good discussion but not a confrontation.</p>
                                                        <p>Let me know you like what I’ve done or said.</p>
                                                        <p>Laugh with me and share in my enjoyment of life.</p>
                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>RELATIONSHIPS</h5><br><br>
                                                        <p>Nines at their best in a relationship are kind, gentle, reassuring, supportive, loyal, and nonjudgmental.</p>
                                                        <p>Nines at their worst in a relationship are stubborn, passive-aggressive, unassertive, overly accommodating, and defensive.</p>

                                                    </a>
                                                </li>
                                                <li class="sticky-notes">
                                                    <a href = "#">
                                                        <h5>CAREERS</h5><br><br>
                                                        <p>Nines listens well, are objective, and make excellent mediators and diplomats. They are frequently in the helping professions. Some prefer structured situations, such as the military, civil service, and other bureaucracies.
                                                        <br>
                                                        When Nines move toward point Three or Six, or their One or Eight wing is strong, they are more aggressive and competitive.
                                                        </p>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <br><br><br>
                            <br><br><br>
                            <br><br><br>
                            <br><br><br>
                            <br><br><br>
                            <br><br><br>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script type="text/javascript" src="../assets/js/enneagram.js"></script>