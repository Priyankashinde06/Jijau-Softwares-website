<?php
// Ensure no output before headers
ob_start();

// Headers first
header('Content-Type: application/json');

// SMTP Configuration
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
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $qualification = filter_input(INPUT_POST, 'qualification', FILTER_SANITIZE_STRING);
    $domain = filter_input(INPUT_POST, 'domain', FILTER_SANITIZE_STRING);
    $experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_STRING);
    $skills = filter_input(INPUT_POST, 'skills', FILTER_SANITIZE_STRING);
    $goals = filter_input(INPUT_POST, 'goals', FILTER_SANITIZE_STRING);
    $questions = filter_input(INPUT_POST, 'questions', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($fullName) || empty($email) || empty($phone) || empty($qualification) || empty($domain)) {
        throw new Exception("All required fields must be filled");
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
        $mail->setFrom($email, $fullName);
        $mail->addAddress(RECIPIENT_EMAIL, RECIPIENT_NAME);
        $mail->addReplyTo($email, $fullName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Training Program Enquiry - $fullName";

        // Build email body
        $emailBody = "<h2>New Training Program Enquiry Received</h2>";
        $emailBody .= "<p><strong>Name:</strong> $fullName</p>";
        $emailBody .= "<p><strong>Email:</strong> <a href=\"mailto:$email\">$email</a></p>";
        $emailBody .= "<p><strong>Phone:</strong> $phone</p>";
        $emailBody .= "<p><strong>Current Qualification:</strong> $qualification</p>";
        $emailBody .= "<p><strong>Training Domain of Interest:</strong> $domain</p>";
        
        if (!empty($experience)) {
            $emailBody .= "<p><strong>Previous Experience:</strong> $experience</p>";
        }
        
        if (!empty($skills)) {
            $emailBody .= "<p><strong>Technical Skills:</strong> $skills</p>";
        }
        
        $emailBody .= "<h3>Training Goals</h3>";
        $emailBody .= "<div style=\"white-space: pre-wrap;\">" . nl2br($goals) . "</div>";
        
        if (!empty($questions)) {
            $emailBody .= "<h3>Questions</h3>";
            $emailBody .= "<div style=\"white-space: pre-wrap;\">" . nl2br($questions) . "</div>";
        }

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace("<br>", "\n", $emailBody));

        $mail->send();

        echo json_encode([
            'status' => 'success',
            'message' => 'Training enquiry submitted successfully!'
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