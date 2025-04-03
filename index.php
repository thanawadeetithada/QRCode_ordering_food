<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {   
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['userrole'];
            $_SESSION['username'] = $user['username']; 

            if ($_SESSION['user_role'] == 'superadmin') {
                header("Location: dashboard.php");
                exit();
            } else if ($_SESSION['user_role'] == 'admin') {
                header("Location: kitchen.php");
                exit();
            } else {
                $error_message = "❌ คุณไม่มีสิทธิ์เข้าถึง";
            }
            
        } else {
            $error_message = "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error_message = "❌ ชื่อผู้ใช้งานนี้ไม่ได้ลงทะเบียน";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แอดมินสั่งอาหาร</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9fafc;
    }

    .login-container {
        background-color: #ffffff;
        padding: 2rem;
        width: 90%;
        max-width: 500px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        transition: box-shadow 0.3s ease;
    }

    .login-container:hover {
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
    }

    p {
        margin-top: 15px;
        font-size: 0.9rem;
        color: #000;
    }

    .login-title {
        color: #000;
        font-size: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        width: 100%;
        padding-left: 20px;

    }

    .login-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;
        justify-content: center;
    }

    .form-group {
        text-align: justify;
    }

    form button {
        width: 100%;
    }

    .login-wrapper i {
        font-size: -webkit-xxx-large;
        color: #045cbb;
    }

    button {
        margin-top: 15px;
    }

    .modal-btn button {
        width: 30%;
        margin: 5px;
    }

    .modal-btn {
        display: flex;
        justify-content: center;
    }
    </style>
</head>

<body>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
    alert("ลงทะเบียนสำเร็จแล้ว กรุณารอยืนยันก่อนเข้าสู่ระบบ");
    </script>
    <?php endif; ?>
    <div class="login-wrapper">
        <h2 class="login-title">แอดมินสั่งอาหาร</h2>

        <div class="login-container">
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="exampleInputUsername">ชื่อผู้ใช้</label>
                    <input type="text" class="form-control" id="exampleInputUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">รหัสผ่าน</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
            </form>
            <p>
                <a href="#" id="forgotPasswordLink" data-toggle="modal" data-target="#forgotPasswordModal">
                    ลืมรหัสผ่าน</a>
                <br> <a href="register.php">ยังไม่มีบัญชี? ลงทะเบียนใหม่</a>
            </p>
        </div>
    </div>

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal">
                <div class="modal-header align-items-center">
                    <h5 class="modal-title mx-auto">ลืมรหัสผ่าน</h5>
                </div>
                <div class="modal-body px-4">
                    <form id="forgotPasswordForm" method="POST" action="process_forgot_password.php">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control rounded-pill"
                                placeholder="กรุณาใส่อีเมล" required>
                        </div>
                        <div class="modal-btn">
                            <button type="submit" class="btn btn-primary">ตกลง</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


</body>

</html>