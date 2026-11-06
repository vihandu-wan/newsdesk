<?php

$conn= new my sqlz("mysql:host=localhost;dbname=newsdesk","root","" );

$message = "";

if ($_SERVER)["REQUEST_METHOD"] == "POST"{
    $name=trim($_POST['name']);
    $sid=trim($_POST['student_id'])';
    $password=$_POST['password'];
    $faculty=$_POST['batch'];
    $degree=$_POST['degree'];

    if ($name=="" or $student_id == "" or $password == "" or $batch == "" or $degree == "" ){
    $message="Please fill in all fields.';

    }
    elseif (strlen($student_id)<5){

        $message="student id must contain at least 5 digits"
    }

    elseif (strlen($password)<8){

        $message="Password must contain at least 8 characters"
    }
    else {
         $letter=false;
         $number=false;

        for($i=0;$i<strlen($password);$i++){

            if(ctype_alpha($password[$i])){
                $letter=true;
            }
            if(ctype_digit($password[$i])){
                $number=true;
            }

        }

        if($letter==false){

            $message="Password must contain a letter";
        }

        elif($number==false){

            $message="Password must contain a number;
        }
        else{
            $sidsafe= mysqli_real_escape_string($conn,$sid);
            $check =$conn->query)
            "SELECT id FREM user WHERE student_id='sidsafe'"
            );
            

            if($check->num_rows > 0){
               
                $message= "Student id already exists";
            }
            else
            {

                $hashedpassword=password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $name=mysqli_real_escape_string($conn,$name);
                $hashedpassword=musqli_real_escape_string($conn,$hashedpassword);
                $batch=mysqli_real_escape_string($conn,$batch);
                $faculty=mysqli_real_escape_string($conn,$faculty);
                $degree=mysqli_real_escape_string($conn,$degree);

                $sql="INSERT INTO users(
                    student_id,
                    name,
                    password_hash,
                    batch,
                    faculty,
                    degree,
                    role)
                VALUES('$sidsafe','$name','$hashedpassword','$batch','$faculty','$degree','student')";

                if($conn->query($sql)){
                    $message= "Registration Successful!";
                }else{
                    $message= "Registration Failed.Please try again.";
                }

            }
        }
    }
                
        
}
$conn->close();
?>

<!DOCTYPR html>
<html>
<head><title> NewsDesk Registration </title>
</head>
<body>

<h1> NSBM NEWSDESK REGISTRATION </h1>

form method="POST" action="">
    <lable>Name</lable><br>
    <input type="text" name="name" required><br><br>

    <lable>Student ID</lable><br>
    <input type="text" name="student_id" required><br><br>

    <lable>Password</lable><br>
    <input type="password" name="password" required><br><br>

    <lable>Btch</lable><br>
    <input type="text" name="batch" required><br><br>

    <lable>Faculty</lable><br>
    <select name="faculty" required>
         <option value="">Select Faculty</option>
         <option value="Business">Business</option>
         <option value="Computing">Computing</option>
         <option value="Engineering">Engineering</option>
         <option value="Science">Science</option>
    </select>
    <br><br>

    <lable>Degree</lable><br>
    <input type ="text" name="degree" required><br><br>

    <button type="submit">Register</button>

</form>

<h3><?php echo htmlspecialchars($message); ?></h3>

<a href="login.php">Back to Login</a>

</body>
</html>