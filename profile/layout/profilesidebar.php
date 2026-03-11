<style>
  .sidebar-menu li a{
    padding: 10px !important;
  }
  .sidebar-menu li a.active{
    background-color: #d9e7ff;
  }
</style>
<div class="col-md-2" id="prof-left">
    <ul class="sidebar-menu">
      <div id="header"><label>Profile</label></div>
      <li>
        <a href="/zen/profile/">
          <p>
            <i class="icon-user-female"></i> Personal
          </p>
        </a>
      </li>
      <li>
        <a href="fam">
          <p>
            <i class="ion-ios-people"></i> Family Background
          </p>
        </a>
      </li>
      <li>
        <a href="skill">
          <p>
            <i class="zmdi zmdi-favorite"></i> Special Skills
          </p>
        </a>
      </li>
      <li>
        <a href="education">
          <p>
            <i class="icon-graduation"></i> Education
          </p>
        </a>
      </li>
      <hr>
      <div id="header"><label>Professional</label></div>
      <li>
        <a href="eligibility">
          <p>
            <i class="icofont icofont-certificate-alt-1"></i> Eligibility & Licenses
          </p>
        </a>
      </li>
      <li>
        <a href="cert">
          <p>
           <i class="icofont icofont-certificate-alt-2"></i> Training & Seminar
          </p>
        </a>
      </li>
      
      <hr>
      <div id="header"><label>Work</label></div>
      <li>
        <a href="emp">
          <p>
            <i class="ion-briefcase"></i> Employment Record
          </p>
        </a>
      </li>
      <li>
        <a href="Intcert">
          <p>
            <i class="icofont icofont-certificate-alt-2"></i> Internal Certificate
          </p>
        </a>
      </li>
      <li>
        <a href="payslip">
          <p>
            <i class="zmdi zmdi-receipt"></i> Payslip
          </p>
        </a>
      </li>
      <li>
        <a href="contract">
          <p>
            <i class="zmdi zmdi-assignment"></i> Contracts
          </p>
        </a>
      </li>
      <li>
        <a href="characterRef">
          <p>
            <i class="zmdi zmdi-accounts-list-alt"></i> Character Reference
          </p>
        </a>
      </li>
      <hr>
      <div id="header"><label>Personality Test</label></div>
      <li>
        <a href="inneg">
          <p>
            <i class="icofont icofont-brainstorming"></i> Enneagram
          </p>
        </a>
      </li>
      <li>
        <a href="tapt">
          <p>
            <i class="icofont icofont-brainstorming"></i> TAPT
          </p>
        </a>
      </li>
      <li>
        <a href="discs">
          <p>
            <i class="icofont icofont-brainstorming"></i> DISC
          </p>
        </a>
      </li>
      <li>
        <a href="miqs">
          <p>
            <i class="icofont icofont-brain-alt"></i> Multiple Intelligent Quotient
          </p>
        </a>
      </li>
      <li>
        <a href="colors">
          <p>
            <i class="icofont icofont-brainstorming"></i> What color are you?
          </p>
        </a>
      </li>
      <li>
        <a href="vaks">
          <p>
            <i class="icofont icofont-brainstorming"></i> VAK
          </p>
        </a>
      </li>
      <hr>
      <li>
        <a href="#">
          <p>
           
          </p>
        </a>
      </li>
    </ul>
    <div style="padding: 15px;">
      True North Group of Companies | 2025
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let path = window.location.pathname.split("/").pop(); 
    if (path === "") path = "profile";

    // Loop through all sidebar links
    document.querySelectorAll("#prof-left .sidebar-menu li a").forEach(function(link) {
        let href = link.getAttribute("href");

        if (href === path || (path.includes(href) && href !== "/")) {
            link.classList.add("active");
        }
    });
});
</script>
