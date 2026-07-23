<?php
if($_SERVER["REQUEST_METHOD"]=="POST"){
$server = "localhost";
$username = "root";
$db_password = "";
$database = "rms";

$connection = mysqli_connect($server, $username, $db_password, $database);
if(!$connection){
    die("Connection to the database failed due to " . mysqli_connect_error());
}
//echo "Successfully Connected to database";
$candidate_name = $_POST['candidate_name'];
$father_name = $_POST['father_name'];
$mother_name = $_POST['mother_name'];
$dob = $_POST['dob'];
$gender = $_POST['gender'];
$social_category = $_POST['category'];
$nationality = $_POST['nationality'];
$religion = $_POST['religion'];
$course = $_POST['course_name'];
$abc_id = $_POST['abc_id'];
$admission_year = $_POST['year_of_admission'];
$email = $_POST['email'];
$phone_number = $_POST['phone'];
$present_address = $_POST['present_address'];
$permanent_address = $_POST['permanent_address'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
if($password != $confirm_password){
    die("Passwords do not match");
}
$check = mysqli_query($connection,"SELECT * FROM student_registration WHERE email='$email'");

if(mysqli_num_rows($check)>0)
{
    die("Email already exists");
}
if(strlen($phone_number)!=10)
{
    die("Invalid Phone Number");
}


$photo = uniqid() . "_" . $_FILES['headshot']['name'];
$tmpPhoto = $_FILES['headshot']['tmp_name'];

$photo_path = "uploads/photos/" . $photo;

$signature = uniqid() . "_" . $_FILES['signature']['name'];
$tmpSignature = $_FILES['signature']['tmp_name'];

$signature_path = "uploads/signatures/" . $signature;

$allowed = ['jpg','jpeg','png'];

$photoExt = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
$signatureExt = strtolower(pathinfo($signature, PATHINFO_EXTENSION));

if(!in_array($photoExt, $allowed)){
    die("Only JPG, JPEG and PNG files are allowed.");
}

if(!in_array($signatureExt, $allowed)){
    die("Only JPG, JPEG and PNG files are allowed.");
}

if(!move_uploaded_file($tmpPhoto, $photo_path)){
    die("Photo upload failed");
}

if(!move_uploaded_file($tmpSignature, $signature_path)){
    die("Signature upload failed");
}



$stmt = $connection->prepare("INSERT INTO student_registration
(
candidate_name,
father_name,
mother_name,
dob,
gender,
social_category,
nationality,
religion,
course,
abc_id,
admission_year,
email,
phone_number,
present_address,
permanent_address,
password,
photo_path,
signature_path
)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
if(!$stmt){
    die("Prepare failed: " . $connection->error);
}
$stmt->bind_param(
"ssssssssssssssssss",
$candidate_name,
$father_name,
$mother_name,
$dob,
$gender,
$social_category,
$nationality,
$religion,
$course,
$abc_id,
$admission_year,
$email,
$phone_number,
$present_address,
$permanent_address,
$hashedPassword,
$photo_path,
$signature_path
);

if($stmt->execute()){
    echo "Registration Successful";
} else {
    echo $stmt->error;
}

$stmt->close();


mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RMS</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <div class="lowersection">
          
             <a href="#" class="lower-sec">HOME</a> 
             <!-- <a href="#" class="lower-sec">SIGN UP</a>   -->
            
            
            <a href="#" class="lower-sec">STUDENT LOGIN</a> 
            <a href="#" class="lower-sec">TEACHER LOGIN</a> 
            <a href="#" class="lower-sec">ADMIN LOGIN</a>   
            <a href="#" class="lower-sec">FEEDBACK</a>  
            <a href="#" class="lower-sec">FAQ</a>   
            <a href="#" class="lower-sec">HELP DESK</a> 
            
            <a href="#" class="lower-sec">CONTACT US</a>    

        </div>

  <main>
    <div class="register">

      <form class="form" action="register.php" method="POST" enctype="multipart/form-data">
        <div class="heading">
          <h2>Registration Form</h2>
        </div>
        <!-- Personal Details -->
        <div class="personal_details">
          <h3>Personal Details:</h3>

          <div class="name">
            <label for="candidate_name">Candidate's Name:</label>
            <input type="text" id="candidate_name" name="candidate_name" class="input" required>
          </div>

          <div class="father-name">
            <label for="father_name">Father's Name:</label>
            <input type="text" id="father_name" name="father_name" class="input" required>
          </div>

          <div class="mother-name">
            <label for="mother_name">Mother's Name:</label>
            <input type="text" id="mother_name" name="mother_name" class="input" required>
          </div>

          <div class="dob">
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" class="input" required>
          </div>

          <div class="category">
            <label for="category">Social Category:</label>
            <input type="text" id="category" name="category" class="input">
          </div>

          <div class="nationality">
            <label for="nationality">Nationality:</label>
            <input type="text" id="nationality" name="nationality" class="input" required>
          </div>

          <div class="religion">
            <label for="religion">Religion:</label>
            <input type="text" id="religion" name="religion" class="input">
          </div>
          <div class="gender">
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" class="input" required>
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <!-- Academic Details -->
        <div class="academic_details">
          <h3>Academic Details:</h3>

          <div class="course_name">
            <label for="course_name">Course:</label>
            <input type="text" id="course_name" name="course_name" class="input" required>
          </div>

          <!-- <div class="enrollment">
            <label for="enrollment_no">Enrollment No.:</label>
            <input type="text" id="enrollment_no" name="enrollment_no" class="input" required>
          </div> -->

          <div class="abc_id">
            <label for="abc_id">ABC ID:</label>
            <input type="text" id="abc_id" name="abc_id" class="input" required>
          </div>

          <div class="ad-year">
            <label for="year_of_admission">Year of Admission:</label>
            <input type="number" id="year_of_admission" name="year_of_admission" class="input">
          </div>
        </div>

        <!-- Contact Details -->
        <div class="contact-details">
          <h3>Contact Details: </h3>

          <div class="email">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="input" required>
          </div>

          <div class="present_address">
            <label for="present_address">Present Address:</label>
            <input type="text" id="present_address" name="present_address" class="input" required>
          </div>

          <div class="permanent_address">
            <label for="permanent_address">Permanent Address:</label>
            <input type="text" id="permanent_address" name="permanent_address" class="input">
          </div>

          <div class="contact_no">
            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" class="input" required>
          </div>
        </div>

        <!-- Account Details -->
        <div class="account_details">
          <h3>Account Details:</h3>

          <div class="password">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="input" required>
          </div>

          <div class="confirm_password">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="input" required>
          </div>
        </div>

        <!-- Documents -->
        <div class="docs">
          <h3>Documents:</h3>

          <div class="sign">
            <label for="signature">Upload Signature:</label>
            <input type="file" id="signature" name="signature" accept="image/*" required>
          </div>

          <div class="headshot">
            <label for="headshot">Upload Photo:</label><br>
            <input type="file" id="headshot" name="headshot" accept="image/*" required>
          </div>
        </div>

        <!-- Confirmation -->
        <div class="confirmation">
          <label for="confirmation">
            I hereby confirm that all the information provided above is accurate.
          </label>

          <input type="checkbox" id="confirmation" name="confirmation" value="1" required>
        </div>

        <!-- Submit -->
        <div class="submission">
          <button type="submit">Register</button>
        </div>

      </form>

    </div>
  </main>

  <footer></footer>

</body>

</html>