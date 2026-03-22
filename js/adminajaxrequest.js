// Ajax Call for admin Login Verification
function checkAdminLogin() {
  window.location.href = "Admin/index.php";
  return false;
}

// Empty Login Fields
function clearAdminLoginField() {
  $("#adminLoginForm").trigger("reset");
}

// Empty Login Fields and Status Msg
function clearAdminLoginWithStatus() {
  $("#statusAdminLogMsg").html(" ");
  clearAdminLoginField();
}
