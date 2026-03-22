<?php
include('./dbConnection.php');
include('./mainInclude/header.php');
?>

<main class="auth-page">
  <div class="container auth-shell">
    <div class="auth-intro text-center">
      <p class="auth-kicker">Student Access</p>
      <h1>Sign in or create your CloudHub account.</h1>
      <p class="auth-subtitle">
        Continue your learning journey or register for a new student account with a cleaner, more polished experience.
      </p>
    </div>

    <div class="row auth-row">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <section class="auth-card auth-card-login">
          <h2>If Already Registered !! Login</h2>
          <p class="auth-card-copy">Access your student area and continue where you left off.</p>

          <form id="stuLoginForm" onsubmit="return false;">
            <div class="form-group">
              <label for="stuLogEmail" class="font-weight-bold">Email</label>
              <input type="email" class="form-control" placeholder="you@example.com" name="stuLogEmail" id="stuLogEmail" autocomplete="off" autocapitalize="none" spellcheck="false">
            </div>

            <div class="form-group">
              <label for="stuLogPass" class="font-weight-bold">Password</label>
              <input type="password" class="form-control" placeholder="Enter your password" name="stuLogPass" id="stuLogPass" autocomplete="new-password">
            </div>

            <button type="button" class="btn btn-primary auth-submit-btn" id="stuLoginBtn" onclick="checkStuLogin()">Login</button>
          </form>

          <small id="statusLogMsg" class="auth-status"></small>
        </section>
      </div>

      <div class="col-lg-6">
        <section class="auth-card auth-card-signup">
          <h2>New User !! Sign Up</h2>
          <p class="auth-card-copy">Create a student account, choose your preferred track, and get started.</p>

          <form id="stuRegForm" onsubmit="return false;">
            <div class="form-group">
              <label for="stuname" class="font-weight-bold">Name</label>
              <small id="statusMsg1" class="auth-inline-status"></small>
              <input type="text" class="form-control" placeholder="Your full name" name="stuname" id="stuname" autocomplete="name">
            </div>

            <div class="form-group">
              <label for="stuemail" class="font-weight-bold">Email</label>
              <small id="statusMsg2" class="auth-inline-status"></small>
              <input type="email" class="form-control" placeholder="you@example.com" name="stuemail" id="stuemail" autocomplete="email">
              <small class="form-text">We’ll only use this to manage your student account.</small>
            </div>

            <div class="form-group">
              <label for="stupass" class="font-weight-bold">New Password</label>
              <small id="statusMsg3" class="auth-inline-status"></small>
              <input type="password" class="form-control" placeholder="At least 6 characters" name="stupass" id="stupass" autocomplete="new-password">
            </div>

            <div class="form-group">
              <label for="preferred_track" class="font-weight-bold">Select Track</label>
              <select class="form-control" id="preferred_track" name="preferred_track">
                <option value="Programming">Programming</option>
                <option value="Networking">Networking</option>
                <option value="Artificial Intelligence">Artificial Intelligence</option>
                <option value="Cyber Security">Cyber Security</option>
                <option value="DevOps">DevOps</option>
                <option value="Data Science">Data Science</option>
                <option value="Operating Systems">Operating Systems</option>
                <option value="AWS Cloud">AWS Cloud</option>
              </select>
            </div>

            <div class="form-group">
              <label for="experience_level" class="font-weight-bold">Experience Level</label>
              <select class="form-control" id="experience_level" name="experience_level">
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
              </select>
            </div>

            <button type="button" class="btn btn-primary auth-submit-btn" id="signup" onclick="addStu()">Sign Up</button>
          </form>

          <small id="successMsg" class="auth-status"></small>
          <small id="jsHealth" class="auth-status auth-status-error"></small>
        </section>
      </div>
    </div>
  </div>
</main>

<script src="js/jquery.min.js"></script>
<script src="js/ajaxrequest.js?v=3"></script>
<script>
  (function () {
    var ok = (typeof window.addStu === 'function');
    if (!ok) {
      var el = document.getElementById('jsHealth');
      if (el) el.textContent = 'JS not loaded: addStu() is missing. Check js/ajaxrequest.js.';
      if (console && console.log) console.log('addStu is missing. ajaxrequest.js not loaded or has errors.');
    }
  })();
</script>

<?php
include('./mainInclude/footer.php');
?>
