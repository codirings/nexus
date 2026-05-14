<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = 'Your message has been sent! We will get back to you soon.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contact - NEXUS</title>
        <link rel="stylesheet" href="stylesheet.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sometype+Mono:wght@400;500;600;700&family=Overpass+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .page-hero{
                background-color:#000;
                min-height:50vh;
                display:flex;
                justify-content:center;
                align-items:center;
                padding:150px 40px 80px
            }
            .page-title{
                font-family:'Space Mono',monospace;
                font-size:clamp(60px,10vw,100px);
                font-weight:700;
                color:#fff;
                letter-spacing:10px;
                text-transform:uppercase
            }
            .contact-section{
                padding:100px 40px;
                background-color:#fff
            }
            .contact-content{
                max-width:700px;
                margin:0 auto
            }
            .contact-info{
                margin-bottom:60px
            }
            .contact-info h2{
                font-family:'Overpass Mono',monospace;
                font-size:clamp(24px,3vw,28px);
                font-weight:600;
                margin-bottom:20px;
                color:#000
            }
            .contact-info p{
                font-family:'Overpass Mono',monospace;
                font-size:clamp(16px,2vw,18px);
                line-height:1.8;
                margin-bottom:15px;
                color:#333
            }
            .contact-form{
                margin-top:40px
            }
            .form-group{
                margin-bottom:25px
            }
            .form-group label{
                font-family:'Overpass Mono',monospace;
                font-size:14px;
                font-weight:600;
                color:#000;
                display:block;
                margin-bottom:10px;
                text-transform:uppercase;
                letter-spacing:1px
            }
            .form-group input,.form-group textarea{
                width:100%;
                padding:15px;
                font-family:'Overpass Mono',monospace;
                font-size:16px;
                border:2px solid #e0e0e0;
                background-color:#f5f5f5;
                color:#000;
                transition:border-color .3s,background-color .3s;
                box-sizing:border-box
            }
            .form-group input:focus,.form-group textarea:focus{
                outline:none;
                border-color:#0099ff;
                background-color:#fff
            }
            .form-group textarea{
                resize:vertical;
                min-height:150px
            }
            .submit-btn{
                font-family:'Overpass Mono',monospace;
                font-size:16px;
                font-weight:600;
                text-transform:uppercase;
                letter-spacing:2px;
                padding:18px 50px;
                background-color:#000;
                color:#fff;
                border:none;
                cursor:pointer;
                transition:background-color .3s,transform .3s
            }
            .submit-btn:hover{
                background-color:#0099ff;
                transform:translateY(-3px)
            }
            .alert{
                padding:12px 16px;
                margin-bottom:20px;
                font-family:'Overpass Mono',monospace;
                font-size:14px
            }
            .alert-error{
                background:#ffe5e5;
                border:1px solid #f44;
                color:#c00
            }
            .alert-success{
                background:#e5ffe5;
                border:1px solid #4b4;
                color:#060
            }
        </style>
    </head>
    <body>
    <?php include 'navbar.php'; ?>

        <section class="page-hero">
            <h1 class="page-title">CONTACT</h1>
        </section>

        <section class="contact-section">
            <div class="contact-content">
                <div class="contact-info">
                    <h2>Get in Touch</h2>
                    <p>Have questions about our products? Want to know more about NEXUS? We're here to help.</p>
                    <p><strong>Location:</strong> Colegio de San Juan de Letran</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="contact.php">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </section>

        <footer class="footer">
            <div class="footer-content">
                <p class="footer-text">Contact us at Colegio de San Juan de Letran</p>
            </div>
        </footer>

        <script src="script.js"></script>
    </body>
</html>