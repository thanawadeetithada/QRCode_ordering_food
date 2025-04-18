<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {  
        $error_message = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน"; 
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // ตรวจสอบว่าอีเมลนี้ถูกใช้งานแล้วหรือไม่
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "อีเมลนี้มีผู้ใช้งานแล้ว";
        } else {
            $userrole = 'user';

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, userrole) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $userrole);

            if ($stmt->execute()) {
                    header("Location: index.php?success=1");
                    exit();
            } else {
                $error_message = "เกิดข้อผิดพลาดในการลงทะเบียน กรุณาลองใหม่";
            }
        }
        $stmt->close();
    }
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบติดตาม</title>
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
        margin-top: 15px;
    }

    .login-wrapper i {
        font-size: -webkit-xxx-large;
        color: #045cbb;
    }

    .alert-danger {
        font-size: 1.2rem;
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <i class="fa-regular fa-file-lines"></i>
        <br>
        <h2 class="login-title">ลงทะเบียนผู้ใช้งานใหม่</h2>
        <div class="login-container">
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="register.php">

                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้</label>
                    <input type="text" class="form-control" id="username" name="username" required
                        value="<?php echo isset($username) ? $username : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" class="form-control" id="email" name="email" required
                        value="<?php echo isset($email) ? $email : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" class="form-control" id="password" name="password" required value="">
                </div>
                <div class="form-group">
                    <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                        value="">
                </div>
                <!-- ปุ่มเข้าสู่ระบบ -->
                <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
            </form>

            <p>
                <a href="index.php">มีบัญชีใช้แล้ว? เข้าสู่ระบบ</a>
            </p>
        </div>
    </div>
</body>

</html>