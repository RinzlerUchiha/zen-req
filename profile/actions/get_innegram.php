<div id="prof-card">
  <div class="basic-info">
     <span id="userName">
      Enneagram Result
     </span>
  </div>
  <div class="basic-info">
    <div style="float: left;">
      <canvas id="polarChart" height="400" width="400"></canvas>
    </div>
    <div style="float: right;">
        <img src="/zen/assets/img/ennea.PNG" width="350" height="300">
    </div>
  </div>  
</div>
 <div class="modal fade" id="Enneagram" tabindex="-1" role="dialog">
  <form id="form_enneagram">
     <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content" id="section1">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_1" name="div_set">
                        <h4>#1</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="1" type="checkbox"  name="set_1" id="set_1_1" style="width: 15px;margin-right:10px;"> <p>(1)  I like to be organized and orderly.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="2" type="checkbox"  name="set_1" id="set_1_2" style="width: 15px;margin-right:10px;"> <p>(2) I want people to feel comfortable coming to me for guidance and advice.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="3" type="checkbox"  name="set_1" id="set_1_3" style="width: 15px;margin-right:10px;"> <p>(3) I’m almost always busy.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="4" type="checkbox"  name="set_1" id="set_1_4" style="width: 15px;margin-right:10px;"> <p>(4) Being understood is very important to me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="5" type="checkbox"  name="set_1" id="set_1_5" style="width: 15px;margin-right:10px;"> <p>(5) I learn from observing or reading as opposed to doing.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="6" type="checkbox"  name="set_1" id="set_1_6" style="width: 15px;margin-right:10px;"> <p>(6) I am nervous around certain authority figures.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="7" type="checkbox"  name="set_1" id="set_1_7" style="width: 15px;margin-right:10px;"> <p>(7) I enjoy life. I am generally uninhibited (outgoing) and optimistic.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="8" type="checkbox"  name="set_1" id="set_1_8" style="width: 15px;margin-right:10px;"> <p>(8) I can be assertive (self-confident) and aggressive when I need to be.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="1" q_category="9" type="checkbox"  name="set_1" id="set_1_9" style="width: 15px;margin-right:10px;"> <p>(9) Sometimes I feel shy and unsure of myself.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                <button type="button" class="btn btn-danger btn-mini waves-effect " data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section2')">Next Page</button>
             </div>
         </div>
         <div class="modal-content hidden" id="section2">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_2" name="div_set">
                        <h4>#2</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="1" type="checkbox"  name="set_2" id="set_2_1" style="width: 15px;margin-right:10px;"> <p>(1) It is difficult for me to be spontaneous.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="2" type="checkbox" name="set_2" id="set_2_2" style="width: 15px;margin-right:10px;"> <p>(2)  Relationships are more important to me than almost anything.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="3" type="checkbox" name="set_2" id="set_2_3" style="width: 15px;margin-right:10px;"> <p>(3)  I like to make to-do lists, progress charts, and schedules for myself.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="4" type="checkbox"  name="set_2" id="set_2_4" style="width: 15px;margin-right:10px;"> <p>(4) My friends say they enjoy my warmth and my different way of looking at life.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="5" type="checkbox"  name="set_2" id="set_2_5" style="width: 15px;margin-right:10px;"> <p>(5) It’s hard to express my feelings in the moment.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="6" type="checkbox"  name="set_2" id="set_2_6" style="width: 15px;margin-right:10px;"> <p>(6) I am often plagued by doubt.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="7" type="checkbox"  name="set_2" id="set_2_7" style="width: 15px;margin-right:10px;"> <p>(7) I don’t like being made to feel obligated or beholden.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="8" type="checkbox"  name="set_2" id="set_2_8" style="width: 15px;margin-right:10px;"> <p>(8) I can’t stand being used or manipulated.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="2" q_category="9" type="checkbox"  name="set_2" id="set_2_9" style="width: 15px;margin-right:10px;"> <p>(9) I often feel in union with nature and people.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section1')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section3')">Next Page</button>
             </div>
         </div>
         <div class="modal-content hidden" id="section3">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_3" name="div_set">
                        <h4>#3</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="1" type="checkbox"  name="set_3" id="set_3_1" style="width: 15px;margin-right:10px;"> <p>(1) I often feel guilty about not getting enough accomplished.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="2" type="checkbox" name="set_3" id="set_3_2" style="width: 15px;margin-right:10px;"> <p>(2)  Sometimes I feel overburdened by the people’s dependence on me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="3" type="checkbox" name="set_3" id="set_3_3" style="width: 15px;margin-right:10px;"> <p>(3)  I don’t mind being asked to work overtime.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="4" type="checkbox"  name="set_3" id="set_3_4" style="width: 15px;margin-right:10px;"> <p>(4) I can become nonfunctional for hours, days, or weeks when I’m depressed.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="5" type="checkbox"  name="set_3" id="set_3_5" style="width: 15px;margin-right:10px;"> <p>(5) I get lost in my interests and like to be alone with them for hours.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="6" type="checkbox"  name="set_3" id="set_3_6" style="width: 15px;margin-right:10px;"> <p>(6) I like to have clear-cut guidelines and to know where I stand.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="7" type="checkbox"  name="set_3" id="set_3_7" style="width: 15px;margin-right:10px;"> <p>(7) I am busy and energetic. I seldom get bored if left to do what I want.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="8" type="checkbox"  name="set_3" id="set_3_8" style="width: 15px;margin-right:10px;"> <p>(8) I value being direct and honest; I put my cards on the table.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="3" q_category="9" type="checkbox"  name="set_3" id="set_3_9" style="width: 15px;margin-right:10px;"> <p>(9) Making choices can be very difficult. I can see the advantages and disadvantages of every option.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section2')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section4')">Next Page</button>
             </div>
         </div>
         <div class="modal-content hidden" id="section4">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_4" name="div_set">
                        <h4>#4</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="1" type="checkbox"  name="set_4" id="set_4_1" style="width: 15px;margin-right:10px;"> <p>(1) I don’t like it when people break rules.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="2" type="checkbox" name="set_4" id="set_4_2" style="width: 15px;margin-right:10px;"> <p>(2)  I have trouble asking for what I need.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="3" type="checkbox" name="set_4" id="set_4_3" style="width: 15px;margin-right:10px;"> <p>(3)  I have an optimistic attitude.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="4" type="checkbox"  name="set_4" id="set_4_4" style="width: 15px;margin-right:10px;"> <p>(4) I am very sensitive to critical remarks and feel hurt at the tiniest slight.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="5" type="checkbox"  name="set_4" id="set_4_5" style="width: 15px;margin-right:10px;"> <p>(5) I usually experience my feelings more deeply when I’m by myself.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="6" type="checkbox"  name="set_4" id="set_4_6" style="width: 15px;margin-right:10px;"> <p>(6) I am always on the alert for danger.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="7" type="checkbox"  name="set_4" id="set_4_7" style="width: 15px;margin-right:10px;"> <p>(7) I often take verbal or physical risks.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="8" type="checkbox"  name="set_4" id="set_4_8" style="width: 15px;margin-right:10px;"> <p>(8) I am an individualist and a nonconformist.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="4" q_category="9" type="checkbox"  name="set_4" id="set_4_9" style="width: 15px;margin-right:10px;"> <p>(9) It is sometimes hard for me to know what I want when I’m with other people.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section3')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section5')">Next Page</button>
             </div>
         </div>
         <div class="modal-content hidden" id="section5">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_5" name="div_set">
                        <h4>#5</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="1" type="checkbox"  name="set_5" id="set_5_1" style="width: 15px;margin-right:10px;"> <p>(1) Incorrect grammar and spelling bother me lot.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="2" type="checkbox" name="set_5" id="set_5_2" style="width: 15px;margin-right:10px;"> <p>(2)  I crave, yet sometimes fear, intimacy.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="3" type="checkbox" name="set_5" id="set_5_3" style="width: 15px;margin-right:10px;"> <p>(3)  I go full force until I get the job done.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="4" type="checkbox"  name="set_5" id="set_5_4" style="width: 15px;margin-right:10px;"> <p>(4) It really affects me emotionally when I read upsetting stories in the newspaper.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="5" type="checkbox"  name="set_5" id="set_5_5" style="width: 15px;margin-right:10px;"> <p>(5) Sometimes I feel guilty that I’m not generous enough.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="6" type="checkbox"  name="set_5" id="set_5_6" style="width: 15px;margin-right:10px;"> <p>(6) I take things too seriously.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="7" type="checkbox"  name="set_5" id="set_5_7" style="width: 15px;margin-right:10px;"> <p>(7) I usually pick upbeat friends who have similar goals.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="8" type="checkbox"  name="set_5" id="set_5_8" style="width: 15px;margin-right:10px;"> <p>(8) I respect people who stand up for themselves.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="5" q_category="9" type="checkbox"  name="set_5" id="set_5_9" style="width: 15px;margin-right:10px;"> <p>(9) Others see me as peaceful, but inside I often feel anxious.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section4')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section6')">Next Page</button>
             </div>
         </div>
         <div class="modal-content hidden" id="section6">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_6" name="div_set">
                        <h4>#6</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="1" type="checkbox"  name="set_6" id="set_6_1" style="width: 15px;margin-right:10px;"> <p>(1) I am idealistic. I want to make the world a better place.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="2" type="checkbox" name="set_6" id="set_6_2" style="width: 15px;margin-right:10px;"> <p>(2)  I am more comfortable giving than receiving.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="3" type="checkbox" name="set_6" id="set_6_3" style="width: 15px;margin-right:10px;"> <p>(3)  I believe in doing things as expediently as possible.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="4" type="checkbox"  name="set_6" id="set_6_4" style="width: 15px;margin-right:10px;"> <p>(4) My ideals are very important to me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="5" type="checkbox"  name="set_6" id="set_6_5" style="width: 15px;margin-right:10px;"> <p>(5) I try to conceal my sensitivity to criticism and judgment.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="6" type="checkbox"  name="set_6" id="set_6_6" style="width: 15px;margin-right:10px;"> <p>(6) I constantly question myself about what might go wrong.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="7" type="checkbox"  name="set_6" id="set_6_7" style="width: 15px;margin-right:10px;"> <p>(7) I’m not an expert in any one thing, but I can do many things well.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="8" type="checkbox"  name="set_6" id="set_6_8" style="width: 15px;margin-right:10px;"> <p>(8) I will go to any lengths to protect those I love.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="6" q_category="9" type="checkbox"  name="set_6" id="set_6_9" style="width: 15px;margin-right:10px;"> <p>(9) Instead of tackling what I really need to do, I sometimes do little, unimportant things.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section5')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section7')">Next Page</button>
             </div>
         </div>
         <div class="modal-content hidden" id="section7">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_7" name="div_set">
                        <h4>#7</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="1" type="checkbox"  name="set_7" id="set_7_1" style="width: 15px;margin-right:10px;"> <p>(1) I am almost always on time.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="2" type="checkbox" name="set_7" id="set_7_2" style="width: 15px;margin-right:10px;"> <p>(2)  I am very sensitive to criticism.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="3" type="checkbox" name="set_7" id="set_7_3" style="width: 15px;margin-right:10px;"> <p>(3)  It is important for people to better themselves and live up to their potential.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="4" type="checkbox"  name="set_7" id="set_7_4" style="width: 15px;margin-right:10px;"> <p>(4) I cry easily. Beauty, love, sorrow, and pain really touch me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="5" type="checkbox"  name="set_7" id="set_7_5" style="width: 15px;margin-right:10px;"> <p>(5) Brash, loud people offend me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="6" type="checkbox"  name="set_7" id="set_7_6" style="width: 15px;margin-right:10px;"> <p>(6) I often experience criticism as an attack.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="7" type="checkbox"  name="set_7" id="set_7_7" style="width: 15px;margin-right:10px;"> <p>(7) My style I to go back and forth from one task to another. I like to keep moving.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="8" type="checkbox"  name="set_7" id="set_7_8" style="width: 15px;margin-right:10px;"> <p>(8) I fight for what is right.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="7" q_category="9" type="checkbox"  name="set_7" id="set_7_9" style="width: 15px;margin-right:10px;"> <p>(9) When there is unpleasantness going on around me, I just try to think about something else for a while.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section6')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section8')">Next Page</button>
             </div>
          </div>
          <div class="modal-content hidden" id="section8">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_8" name="div_set">
                        <h4>#8</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="1" type="checkbox"  name="set_8" id="set_8_1" style="width: 15px;margin-right:10px;"> <p>(1) I hold on to resentment (anger/bitterness) for a long time.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="2" type="checkbox" name="set_8" id="set_8_2" style="width: 15px;margin-right:10px;"> <p>(2)  I work hard to overcome all obstacles in a relationship.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="3" type="checkbox" name="set_8" id="set_8_3" style="width: 15px;margin-right:10px;"> <p>(3)  I’m not interested in talking a lot about my personal life.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="4" type="checkbox"  name="set_8" id="set_8_4" style="width: 15px;margin-right:10px;"> <p>(4)  My melancholy (sad) moods are real and important. I don’t necessarily want to get out of them.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="5" type="checkbox"  name="set_8" id="set_8_5" style="width: 15px;margin-right:10px;"> <p>(5) Conforming is distasteful to me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="6" type="checkbox"  name="set_8" id="set_8_6" style="width: 15px;margin-right:10px;"> <p>(6) I often obsess about what my partner is thinking.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="7" type="checkbox"  name="set_8" id="set_8_7" style="width: 15px;margin-right:10px;"> <p>(7) I seem to let go of grievances and recover loss faster than most people I know.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="8" type="checkbox"  name="set_8" id="set_8_8" style="width: 15px;margin-right:10px;"> <p>(8) I support the underdog (loser).</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="8" q_category="9" type="checkbox"  name="set_8" id="set_8_9" style="width: 15px;margin-right:10px;"> <p>(9) I usually prefer walking away from a disagreement to confronting someone.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section7')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section9')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section9">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_9" name="div_set">
                        <h4>#9</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="1" type="checkbox"  name="set_9" id="set_9_1" style="width: 15px;margin-right:10px;"> <p>(1) I think of myself as being practical, reasonable, and realistic.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="2" type="checkbox" name="set_9" id="set_9_2" style="width: 15px;margin-right:10px;"> <p>(2)  I try to be as sensitive and tactful as possible.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="3" type="checkbox" name="set_9" id="set_9_3" style="width: 15px;margin-right:10px;"> <p>(3)  I try not to let illness stop me from doing anything.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="4" type="checkbox"  name="set_9" id="set_9_4" style="width: 15px;margin-right:10px;"> <p>(4) I often long for what others have.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="5" type="checkbox"  name="set_9" id="set_9_5" style="width: 15px;margin-right:10px;"> <p>(5) I like to associate with others who have expertise in my field.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="6" type="checkbox"  name="set_9" id="set_9_6" style="width: 15px;margin-right:10px;"> <p>(6) I can be a very hard worker.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="7" type="checkbox"  name="set_9" id="set_9_7" style="width: 15px;margin-right:10px;"> <p>(7) I like myself and I’m good to myself.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="8" type="checkbox"  name="set_9" id="set_9_8" style="width: 15px;margin-right:10px;"> <p>(8) Making decisions is not difficult for me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="9" q_category="9" type="checkbox"  name="set_9" id="set_9_9" style="width: 15px;margin-right:10px;"> <p>(9) If I don’t have some routine and structure in my day, I get almost nothing done.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section8')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section10')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section10">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_10" name="div_set">
                        <h4>#10</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="1" type="checkbox"  name="set_10" id="set_10_1" style="width: 15px;margin-right:10px;"> <p>(1) When jealous, I become fearful and competitive.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="2" type="checkbox" name="set_10" id="set_10_2" style="width: 15px;margin-right:10px;"> <p>(2)  When I am alone I know what I want, but when I am with others I am not sure.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="3" type="checkbox" name="set_10" id="set_10_3" style="width: 15px;margin-right:10px;"> <p>(3)  I hate to see jobs undone.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="4" type="checkbox"  name="set_10" id="set_10_4" style="width: 15px;margin-right:10px;"> <p>(4) I try to support my friends, especially when they are in crisis.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="5" type="checkbox"  name="set_10" id="set_10_5" style="width: 15px;margin-right:10px;"> <p>(5) l like having title (doctor, professor, administrator) to feel proud of.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="6" type="checkbox"  name="set_10" id="set_10_6" style="width: 15px;margin-right:10px;"> <p>(6) My friends think of me as loyal, supportive, and compassionate.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="7" type="checkbox"  name="set_10" id="set_10_7" style="width: 15px;margin-right:10px;"> <p>(7) I like people and they usually like me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="8" type="checkbox"  name="set_10" id="set_10_8" style="width: 15px;margin-right:10px;"> <p>(8) Self-reliance and independence are important.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="10" q_category="9" type="checkbox"  name="set_10" id="set_10_9" style="width: 15px;margin-right:10px;"> <p>(9) I tend to put things off until the last minute, but I almost always get them done.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section9')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section11')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section11">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_11" name="div_set">
                        <h4>#11</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="1" type="checkbox"  name="set_11" id="set_11_1" style="width: 15px;margin-right:10px;"> <p>(1) Either I don’t have enough time to relax or I think I shouldn’t relax.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="2" type="checkbox" name="set_11" id="set_11_2" style="width: 15px;margin-right:10px;"> <p>(2)  It is very important that others feel comfortable and welcome in my home.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="3" type="checkbox" name="set_11" id="set_11_3" style="width: 15px;margin-right:10px;"> <p>(3)  I tend to put work before other things.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="4" type="checkbox"  name="set_11" id="set_11_4" style="width: 15px;margin-right:10px;"> <p>(4) I live in the past and in the future more than in present-day reality.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="5" type="checkbox"  name="set_11" id="set_11_5" style="width: 15px;margin-right:10px;"> <p>(5) I have been accused of being negative, cynical, and suspicious.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="6" type="checkbox"  name="set_11" id="set_11_6" style="width: 15px;margin-right:10px;"> <p>(6) I’ve been told I have a good sense of humor.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="7" type="checkbox"  name="set_11" id="set_11_7" style="width: 15px;margin-right:10px;"> <p>(7) I usually manage to get I want.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="8" type="checkbox"  name="set_11" id="set_11_8" style="width: 15px;margin-right:10px;"> <p>(8) I have overindulged in food or drugs.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="11" q_category="9" type="checkbox"  name="set_11" id="set_11_9" style="width: 15px;margin-right:10px;"> <p>(9) I like to be calm and unhurried, but sometimes I overextend myself.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section10')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section12')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section12">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_12" name="div_set">
                        <h4>#12</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="1" type="checkbox"  name="set_12" id="set_12_1" style="width: 15px;margin-right:10px;"> <p>(1) I tend to see things in terms of right and wrong, good or bad.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="2" type="checkbox" name="set_12" id="set_12_2" style="width: 15px;margin-right:10px;"> <p>(2)  I don’t want my dependence to show.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="3" type="checkbox" name="set_12" id="set_12_3" style="width: 15px;margin-right:10px;"> <p>(3)  I can’t understand people who are bored. I never run out of things to do</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="4" type="checkbox"  name="set_12" id="set_12_4" style="width: 15px;margin-right:10px;"> <p>(4) I place great importance on my intuition.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="5" type="checkbox"  name="set_12" id="set_12_5" style="width: 15px;margin-right:10px;"> <p>(5) When I feel socially uncomfortable, I often wish I could disappear.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="6" type="checkbox"  name="set_12" id="set_12_6" style="width: 15px;margin-right:10px;"> <p>(6) I follow rules closely (a phobic trait); or often break rules (a counterphobic trait).</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="7" type="checkbox"  name="set_12" id="set_12_7" style="width: 15px;margin-right:10px;"> <p>(7) I value quick wit.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="8" type="checkbox"  name="set_12" id="set_12_8" style="width: 15px;margin-right:10px;"> <p>(8) Some people take offense at my bluntness (frankness).</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="12" q_category="9" type="checkbox"  name="set_12" id="set_12_9" style="width: 15px;margin-right:10px;"> <p>(9) When people try to tell me what to do or try to control me, I get stubborn.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section11')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section13')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section13">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_13" name="div_set">
                        <h4>#13</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="1" type="checkbox"  name="set_13" id="set_13_1" style="width: 15px;margin-right:10px;"> <p>(1) I analyze major purchases very thoroughly before I make them.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="2" type="checkbox" name="set_13" id="set_13_2" style="width: 15px;margin-right:10px;"> <p>(2) Watching violence on television and seeing people suffer is unbearable.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="3" type="checkbox" name="set_13" id="set_13_3" style="width: 15px;margin-right:10px;"> <p> (3) It is sometimes difficult for me to get in touch with my feelings.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="4" type="checkbox"  name="set_13" id="set_13_4" style="width: 15px;margin-right:10px;"> <p>(4) I try to control people at times.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="5" type="checkbox"  name="set_13" id="set_13_5" style="width: 15px;margin-right:10px;"> <p>(5) I am often reluctant to be assertive or aggressive.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="6" type="checkbox"  name="set_13" id="set_13_6" style="width: 15px;margin-right:10px;"> <p>(6) The more vulnerable I am in my intimate relationship, the more anxious and testy I become.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="7" type="checkbox"  name="set_13" id="set_13_7" style="width: 15px;margin-right:10px;"> <p>(7) I am idealistic. I want to contribute something to the world.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="8" type="checkbox"  name="set_13" id="set_13_8" style="width: 15px;margin-right:10px;"> <p>(8) When I enter a new group, I know immediately who the most powerful person is.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="13" q_category="9" type="checkbox"  name="set_13" id="set_13_9" style="width: 15px;margin-right:10px;"> <p>(9) I like to be sure to have time in my day of relaxing.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section12')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section14')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section14">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_14" name="div_set">
                        <h4>#14</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="1" type="checkbox"  name="set_14" id="set_14_1" style="width: 15px;margin-right:10px;"> <p>(1) I dread (fear) being criticized or judged by others.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="2" type="checkbox" name="set_14" id="set_14_2" style="width: 15px;margin-right:10px;"> <p>(2)  Sometimes I feel a deep sense of loneliness.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="3" type="checkbox" name="set_14" id="set_14_3" style="width: 15px;margin-right:10px;"> <p> (3) I work very hard to take care of and provide for my family.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="4" type="checkbox"  name="set_14" id="set_14_4" style="width: 15px;margin-right:10px;"> <p>(4) I hate insincerity and lack of integrity in others.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="5" type="checkbox"  name="set_14" id="set_14_5" style="width: 15px;margin-right:10px;"> <p>(5) I dislike most social events. I’d rather be alone or with few people I know well.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="6" type="checkbox"  name="set_14" id="set_14_6" style="width: 15px;margin-right:10px;"> <p>(6) I tend to either procrastinate or plunge headlong, even into dangerous situations.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="7" type="checkbox"  name="set_14" id="set_14_7" style="width: 15px;margin-right:10px;"> <p>(7) I vacillate (hesitate) between feeling committed and wanting my freedom and independence.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="8" type="checkbox"  name="set_14" id="set_14_8" style="width: 15px;margin-right:10px;"> <p>(8) I work hard and I know how to get thing done.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="14" q_category="9" type="checkbox"  name="set_14" id="set_14_9" style="width: 15px;margin-right:10px;"> <p>(9) I enjoy just hanging out with my partner or friends.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section13')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section15')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section15">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_15" name="div_set">
                        <h4>#15</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="1" type="checkbox"  name="set_15" id="set_15_1" style="width: 15px;margin-right:10px;"> <p>(1) I often compare myself with others.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="2" type="checkbox" name="set_15" id="set_15_2" style="width: 15px;margin-right:10px;"> <p>(2)  If I don’t get the closeness I need, I feel sad, hurt, and unimportant.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="3" type="checkbox" name="set_15" id="set_15_3" style="width: 15px;margin-right:10px;"> <p> (3) I like identifying with competent groups or important people.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="4" type="checkbox"  name="set_15" id="set_15_4" style="width: 15px;margin-right:10px;"> <p>(4) I have spent years longing for the great love of my life to come along.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="5" type="checkbox"  name="set_15" id="set_15_5" style="width: 15px;margin-right:10px;"> <p>(5) I sometimes feel shy or awkward.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="6" type="checkbox"  name="set_15" id="set_15_6" style="width: 15px;margin-right:10px;"> <p>(6) I am very aware of people trying to manipulate me with flattery.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="7" type="checkbox"  name="set_15" id="set_15_7" style="width: 15px;margin-right:10px;"> <p>(7) I am often at ease in groups.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="8" type="checkbox"  name="set_15" id="set_15_8" style="width: 15px;margin-right:10px;"> <p>(8) In a group I am sometimes an observer rather than a participant.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="15" q_category="9" type="checkbox"  name="set_15" id="set_15_9" style="width: 15px;margin-right:10px;"> <p>(9) Supportive and harmonious relationships are very important to me.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section14')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section16')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section16">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_16" name="div_set">
                        <h4>#16</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="1" type="checkbox"  name="set_16" id="set_16_1" style="width: 15px;margin-right:10px;"> <p>(1) Truth and justice are very important to me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="2" type="checkbox" name="set_16" id="set_16_2" style="width: 15px;margin-right:10px;"> <p>(2)  Sometimes I get physically ill and emotionally drained from taking care of everyone else.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="3" type="checkbox" name="set_16" id="set_16_3" style="width: 15px;margin-right:10px;"> <p>(3) I try to present myself well and make a good first impression.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="4" type="checkbox"  name="set_16" id="set_16_4" style="width: 15px;margin-right:10px;"> <p>(4) I focus on what is wrong with me rather that what is right.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="5" type="checkbox"  name="set_16" id="set_16_5" style="width: 15px;margin-right:10px;"> <p>(5) I get tired when I’m with people for too long.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="6" type="checkbox"  name="set_16" id="set_16_6" style="width: 15px;margin-right:10px;"> <p>(6) I like predictability.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="7" type="checkbox"  name="set_16" id="set_16_7" style="width: 15px;margin-right:10px;"> <p>(7) When people are unhappy, I usually try to get them lighten up and see the bright side.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="8" type="checkbox"  name="set_16" id="set_16_8" style="width: 15px;margin-right:10px;"> <p>(8) I like excitement and stimulation.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="16" q_category="9" type="checkbox"  name="set_16" id="set_16_9" style="width: 15px;margin-right:10px;"> <p>(9) I am very sensitive about being judged and take criticism personally.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section15')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section17')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section17">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_17" name="div_set">
                        <h4>#17</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="1" type="checkbox"  name="set_17" id="set_17_1" style="width: 15px;margin-right:10px;"> <p>(1) I often feel that time is running out and there is too much left to do.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="2" type="checkbox" name="set_17" id="set_17_2" style="width: 15px;margin-right:10px;"> <p>(2) I often figure out what others would like in a person, then act that way.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="3" type="checkbox" name="set_17" id="set_17_3" style="width: 15px;margin-right:10px;"> <p>(3) Financial security is extremely important to me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="4" type="checkbox"  name="set_17" id="set_17_4" style="width: 15px;margin-right:10px;"> <p>(4) I like to be seen as one of a kind.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="5" type="checkbox"  name="set_17" id="set_17_5" style="width: 15px;margin-right:10px;"> <p>(5) I feel different from most people.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="6" type="checkbox"  name="set_17" id="set_17_6" style="width: 15px;margin-right:10px;"> <p>(6) I have sabotaged my own success.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="7" type="checkbox"  name="set_17" id="set_17_7" style="width: 15px;margin-right:10px;"> <p>(7) I love excitement and travel.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="8" type="checkbox"  name="set_17" id="set_17_8" style="width: 15px;margin-right:10px;"> <p>(8) Sometimes I like to spar (fight, argue) with people, especially when I feel safe.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="17" q_category="9" type="checkbox"  name="set_17" id="set_17_9" style="width: 15px;margin-right:10px;"> <p>(9) I like to listen and give people support.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section16')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section18')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section18">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_18" name="div_set">
                        <h4>#18</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="1" type="checkbox"  name="set_18" id="set_18_1" style="width: 15px;margin-right:10px;"> <p>(1) I almost always do what I say I will do.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="2" type="checkbox" name="set_18" id="set_18_2" style="width: 15px;margin-right:10px;"> <p>(2)  I enjoy giving compliments and telling people that they are special to me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="3" type="checkbox" name="set_18" id="set_18_3" style="width: 15px;margin-right:10px;"> <p>(3)  I generally feel pretty good about myself.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="4" type="checkbox"  name="set_18" id="set_18_4" style="width: 15px;margin-right:10px;"> <p>(4) I am always searching for my true self.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="5" type="checkbox"  name="set_18" id="set_18_5" style="width: 15px;margin-right:10px;"> <p>(5) I feel invisible. It surprises me when anyone notices anything about me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="6" type="checkbox"  name="set_18" id="set_18_6" style="width: 15px;margin-right:10px;"> <p>(6) I can support people through think and thin.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="7" type="checkbox"  name="set_18" id="set_18_7" style="width: 15px;margin-right:10px;"> <p>(7) Sometimes I feel inferior and sometimes I feel superior to others.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="8" type="checkbox"  name="set_18" id="set_18_8" style="width: 15px;margin-right:10px;"> <p>(8) I am vulnerable and loving when I really trust someone.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="18" q_category="9" type="checkbox"  name="set_18" id="set_18_9" style="width: 15px;margin-right:10px;"> <p>(9) I focus more on the positive than the negative.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section17')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section19')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section19">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_19" name="div_set">
                        <h4>#19</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="1" type="checkbox"  name="set_19" id="set_19_1" style="width: 15px;margin-right:10px;"> <p>(1) I worry almost constantly.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="2" type="checkbox" name="set_19" id="set_19_2" style="width: 15px;margin-right:10px;"> <p>(2)  I am attracted to being with important or powerful people.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="3" type="checkbox" name="set_19" id="set_19_3" style="width: 15px;margin-right:10px;"> <p>(3)  People often look to me to run the show.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="4" type="checkbox"  name="set_19" id="set_19_4" style="width: 15px;margin-right:10px;"> <p>(4) Sometimes I feel very uncomfortable and different, like an isolated outsider, even when I’m with my friends.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="5" type="checkbox"  name="set_19" id="set_19_5" style="width: 15px;margin-right:10px;"> <p>(5) I don’t look for material possessions to make me unhappy.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="6" type="checkbox"  name="set_19" id="set_19_6" style="width: 15px;margin-right:10px;"> <p>(6) Being neat and orderly helps me feel more in control of my life.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="7" type="checkbox"  name="set_19" id="set_19_7" style="width: 15px;margin-right:10px;"> <p>(7) I usually say whatever is on my mind. Sometimes it gets me into trouble.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="8" type="checkbox"  name="set_19" id="set_19_8" style="width: 15px;margin-right:10px;"> <p>(8) Overly nice or flattering people bother me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="19" q_category="9" type="checkbox"  name="set_19" id="set_19_9" style="width: 15px;margin-right:10px;"> <p>(9) I have trouble getting rid of things.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section18')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" onclick="goToNextDiv('section20')">Next Page</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section20">
             <div class="modal-header">
                 <h4 class="modal-title">Enneagram Test</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               <p> Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</p>

               <div id="personal-form">
                    <center><label style="font-family: Lucida Handwriting; font-size: 20px;">MY PERSONAL INVENTORY</label></center>
                    <div class="container-fluid" style="border: 1px solid lightgrey;" id="div_set_20" name="div_set">
                        <h4>#20</h4>

                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="1" type="checkbox"  name="set_20" id="set_20_1" style="width: 15px;margin-right:10px;"> <p>(1) I love making every detail perfect.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="2" type="checkbox" name="set_20" id="set_20_2" style="width: 15px;margin-right:10px;"> <p>(2)  People have said I exaggerate too much and am overly emotional.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="3" type="checkbox" name="set_20" id="set_20_3" style="width: 15px;margin-right:10px;"> <p>(3)  I like to stand out in some way.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="4" type="checkbox"  name="set_20" id="set_20_4" style="width: 15px;margin-right:10px;"> <p>(4) When people tell me what to do, I often become rebellious and do, or wish I could do, the opposite.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="5" type="checkbox"  name="set_20" id="set_20_5" style="width: 15px;margin-right:10px;"> <p>(5) Acting calm is a defense. It makes me feel stronger.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="6" type="checkbox"  name="set_20" id="set_20_6" style="width: 15px;margin-right:10px;"> <p>(6) I dislike pretension in people.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="7" type="checkbox"  name="set_20" id="set_20_7" style="width: 15px;margin-right:10px;"> <p>(7) I can make great sacrifices to help people.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="8" type="checkbox"  name="set_20" id="set_20_8" style="width: 15px;margin-right:10px;"> <p>(8) Pretense is particularly distasteful to me.</p> </div>
                      </label>
                      <label style="display: block;font-family: Courier New; font-weight: normal;">
                        <div id="sex"><input q_set="20" q_category="9" type="checkbox"  name="set_20" id="set_20_9" style="width: 15px;margin-right:10px;"> <p>(9) I operate under the principle of inertia: If I’m going, it’s easy to keep going but I sometimes have hard time getting started.</p> </div>
                      </label>
                    </div>
               </div>
             </div>
             <div class="modal-footer" id="footer">
                 <button type="button" class="btn btn-secondary btn-mini" onclick="goToPreviousDiv('section19')">Previous Page</button>
                  <button type="button" class="btn btn-primary btn-mini" id="save-enneagram" onclick="goToPreviousDiv('section21')">Submit Answer</button>
             </div>
         </div>
          <div class="modal-content hidden" id="section21">
             <div class="modal-header">
                 <h4 class="modal-title">You Score:</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true"><i style="font-size: 30px;" class="fa fa-times-circle"></i></span>
                 </button>
             </div>
             <div class="modal-body" style="padding: 5px !important;">
               

               <div id="personal-form">
                    

               </div>
             </div>
             <div class="modal-footer" id="footer">
                  <button type="button" class="btn btn-danger btn-mini" data-dismiss="modal">Close</button>
             </div>
         </div>

     </div>
   </form>
 </div>