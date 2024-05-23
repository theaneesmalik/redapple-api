<?php
include "../headers.php";
use PHPMailer\PHPMailer\PHPMailer;

require_once "../_components/phpmailer/Exception.php";
require_once "../_components/phpmailer/PHPMailer.php";
require_once "../_components/phpmailer/SMTP.php";

$mail = new PHPMailer(true);
include ('../jwt.php');
$arr = [];
$SECRET_KEY = 'e%^)urD$RS7QxcsP]p4zm42A7!i[x35YJ](gJKz9qRaMk#B&hH';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $emailInput = $_POST['email'];
        $query = "SELECT * FROM customers WHERE `email`='$emailInput'";
        $result = mysqli_query($dbCon, $query) or die("database error:" . mysqli_error($dbCon));
        $userCheck = mysqli_num_rows($result);
        if ($userCheck) {
            $passwordInput = $_POST['password'];
            $passcheck = mysqli_fetch_assoc($result);
            if ($passwordInput == $passcheck['password']) {
                $email=$passcheck['email'];
                ///////////////////////////
                $code = rand(999999, 111111);
                $query2 = "UPDATE `customers` SET `fa_code` = '$code' WHERE `email`= '$email'";
                ($result2 = mysqli_query($dbCon, $query2)) or die("database error:" . mysqli_error($dbCon));
                if ($result2)
                {
                    try
                    {
                        $mail->isSMTP();
                        $mail->Host = "smtp.gmail.com";
                        $mail->SMTPAuth = true;
                        $mail->Username = "info.iamredapple@gmail.com"; //Gmail adress which you want to use as SMTP server
                        $mail->Password = "tumzauxxcmihiazd"; //Password of above gmail adress
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = "587";

                        $mail->setFrom("info.iamredapple@gmail.com"); //Gmail adress which you want to use as SMTP server
                        $mail->addAddress($email); //Adress at which you want to recieve the data filled in the form
                        $mail->isHTML(true);
                        $mail->Subject = "2FA Verification Code";
                        // $mail->Body = "Enter this code to reset your password Password: <br> <h1 style='text-align:center'> $code </h1>";
                        $mail->Body = "<pre style='font-size:15px'>
                    
2FA Authintication code?

Use this verification code to login!

<h1>$code</h1>

If you did not attempt login, you can ignore this email. It is secure.

                    </pre>";
                        $mail->send();
                        $arr["res"] = "true";
                        $arr["email"] = $email;
                    }
                    catch(Exception $e)
                    {
                        $arr['chk'] = 'inside catch';
                        $arr["res"] = $e;
                    }
                }
                /////////////////////
            } else { 
                $arr['res'] = 'Password Incorrent';
            }
        } else { 
            $arr['res'] = 'Email Does not Exist';
        }
    } 
     else if (isset($_POST['code']))
    {
        $code = $_POST["code"];
        $email = $_POST["codeEmail"];
        $query3 = "SELECT * FROM `customers` WHERE `email`='$email'";
        ($result3 = mysqli_query($dbCon, $query3)) or die("database error:" . mysqli_error($dbCon));
        $data = mysqli_fetch_assoc($result3);
        if ($data["fa_code"] == $code)
        {
            $query4 = "UPDATE `customers` SET `fa_code` = NULL WHERE `email`='$email'";
            ($result4 = mysqli_query($dbCon, $query4)) or
            die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $result4;
             $payload = [
                    'iat' => time(),
                    'iss' => 'localhost:3000',
                    'exp' => time() + (360*24*60*60), // 10 hrs
                    'userID' => $data['Id']
                ];
                $token = JWT::encode($payload,$SECRET_KEY);
                $arr['email'] = $data['email'];
                $arr['id'] = $data['Id'];
                $arr['token'] = $token;
        }
        else
        {
            $arr["res"] = false;
        }
    }
}
print(json_encode($arr));
?>

