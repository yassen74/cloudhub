$(document).ready(function() {
  $("#stuemail").on("keypress blur", function() {
    var reg = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
    var stuemail = $("#stuemail").val();

    $.ajax({
      url: "Student/addstudent.php",
      type: "post",
      dataType: "json",
      data: {
        checkemail: "checkmail",
        stuemail: stuemail
      },
      success: function(data) {
        if (data != 0) {
          $("#statusMsg2").html('<small style="color:red;"> Email ID Already Registered ! </small>');
          $("#signup").attr("disabled", true);
        } else if (data == 0 && reg.test(stuemail)) {
          $("#statusMsg2").html('<small style="color:green;"> There you go ! </small>');
          $("#signup").attr("disabled", false);
        } else if (!reg.test(stuemail)) {
          $("#statusMsg2").html('<small style="color:red;"> Please Enter Valid Email e.g. example@mail.com </small>');
          $("#signup").attr("disabled", false);
        }
        if (stuemail == "") {
          $("#statusMsg2").html('<small style="color:red;"> Please Enter Email ! </small>');
        }
      },
      error: function(xhr) {
        $("#statusMsg2").html('<small style="color:red;"> Email check failed. </small>');
        console.log("checkemail ajax error:", xhr.status, xhr.responseText);
      }
    });
  });

  $("#stuname").keypress(function() {
    if ($("#stuname").val() !== "") {
      $("#statusMsg1").html(" ");
    }
  });

  $("#stupass").keypress(function() {
    if ($("#stupass").val() !== "") {
      $("#statusMsg3").html(" ");
    }
  });
});

function addStu() {
  try {
    var stuname = $("#stuname").val().trim();
    var stuemail = $("#stuemail").val().trim();
    var stupass = $("#stupass").val();

    $("#statusMsg1, #statusMsg2, #statusMsg3, #successMsg").html("");

    if (!stuname) {
      $("#statusMsg1").html('<small style="color:red;">Please enter name</small>');
      return;
    }
    if (!stuemail) {
      $("#statusMsg2").html('<small style="color:red;">Please enter email</small>');
      return;
    }
    if (!stupass || stupass.length < 6) {
      $("#statusMsg3").html('<small style="color:red;">Password must be at least 6 characters</small>');
      return;
    }

    $.ajax({
      url: "Student/addstudent.php",
      method: "POST",
      dataType: "json",
      data: {
        stusignup: 1,
        stuname: stuname,
        stuemail: stuemail,
        stupass: stupass
      },
      success: function(data) {
        if (data && data.status === "ok") {
          $("#successMsg").html('<span style="color:green;">Registration successful. You can login now.</span>');
          $("#stuRegForm")[0].reset();
        } else if (data && data.status === "duplicate_email") {
          $("#successMsg").html('<span style="color:red;">Email already registered.</span>');
        } else if (data && data.message) {
          $("#successMsg").html('<span style="color:red;">' + data.message + '</span>');
        } else {
          $("#successMsg").html('<span style="color:red;">Registration failed.</span>');
        }
      },
      error: function(xhr) {
        $("#successMsg").html('<span style="color:red;">Request failed. Check console.</span>');
        console.log("addStu ajax error:", xhr.status, xhr.responseText);
      }
    });
  } catch (e) {
    console.log("addStu exception:", e);
  }
}

function checkStuLogin() {
  try {
    var email = $("#stuLogEmail").val().trim();
    var pass = $("#stuLogPass").val();

    $("#statusLogMsg").html("");

    if (!email || !pass) {
      $("#statusLogMsg").html('<small style="color:red;">Please enter email and password</small>');
      return;
    }

    $.ajax({
      url: "Student/stulogin.php",
      method: "POST",
      dataType: "json",
      data: {
        checkLogemail: email,
        checkLogpass: pass
      },
      success: function(data) {
        if (data === 1 || data === "1") {
          window.location.replace("Student/myprofile.php");
        } else {
          $("#statusLogMsg").html('<small style="color:red;">Invalid email or password</small>');
        }
      },
      error: function(xhr) {
        $("#statusLogMsg").html('<small style="color:red;">Login request failed. Check console.</small>');
        console.log("login ajax error:", xhr.status, xhr.responseText);
      }
    });
  } catch (e) {
    console.log("checkStuLogin exception:", e);
  }
}
