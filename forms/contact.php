<?php
// Ensure no output before headers
ob_start();

// Headers first
header('Content-Type: application/json');

// SMTP Configuration - Use the same as in internship.php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'ps662001@gmail.com');
define('SMTP_PASS', 'npcqieiamcjwavfp'); // Use app password for Gmail
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('RECIPIENT_EMAIL', 'priyanka.jijausoftwares@gmail.com');
define('RECIPIENT_NAME', 'Jijau Software HR');

// Error handling wrapper
try {
    // Only process POST requests
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Include PHPMailer - adjust path as needed
    require 'C:/xampp/htdocs/Jijau Software/PHPMailer-master/src/PHPMailer.php';
    require 'C:/xampp/htdocs/Jijau Software/PHPMailer-master/src/SMTP.php';
    require 'C:/xampp/htdocs/Jijau Software/PHPMailer-master/src/Exception.php';

    // Get and sanitize form data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $agree = isset($_POST['agree']) ? true : false;

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception("Name, email and message are required fields");
    }

    // Check if privacy policy is agreed
    if (!$agree) {
        throw new Exception("You must agree to the privacy policy");
    }

    // Create PHPMailer instance
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress(RECIPIENT_EMAIL, RECIPIENT_NAME);
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Contact Form Submission - $name";

        // Build email body
        $emailBody = "<h2>New Contact Form Submission</h2>";
        $emailBody .= "<p><strong>Name:</strong> $name</p>";
        $emailBody .= "<p><strong>Email:</strong> <a href=\"mailto:$email\">$email</a></p>";
        $emailBody .= "<p><strong>Phone:</strong> " . ($phone ? $phone : 'Not provided') . "</p>";
        
        $emailBody .= "<h3>Message</h3>";
        $emailBody .= "<div style=\"white-space: pre-wrap;\">" . nl2br($message) . "</div>";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace("<br>", "\n", $emailBody));

        $mail->send();

        echo json_encode([
            'status' => 'success',
            'message' => 'Your message has been sent successfully!'
        ]);
        
    } catch (Exception $e) {
        throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Ensure no extra output
ob_end_flush();
?>