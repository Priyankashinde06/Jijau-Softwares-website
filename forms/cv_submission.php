<?php
// Ensure no output before headers
ob_start();

// Headers first
header('Content-Type: application/json');

// SMTP Configuration (same as your contactTest.php)
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
    $currentLocation = filter_input(INPUT_POST, 'currentLocation', FILTER_SANITIZE_STRING);
    $position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
    $skills = filter_input(INPUT_POST, 'skills', FILTER_SANITIZE_STRING);
    $experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_STRING);
    $coverLetter = filter_input(INPUT_POST, 'coverLetter', FILTER_SANITIZE_STRING);
    $portfolio = filter_input(INPUT_POST, 'portfolio', FILTER_SANITIZE_URL);

    // Validate required fields
    if (empty($fullName) || empty($email) || empty($phone) || empty($currentLocation)) {
        throw new Exception("All required fields must be filled");
    }

    // Handle file upload
    $resumePath = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__.'/../uploads/resumes/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
        ];
        
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $_FILES['resume']['tmp_name']);
        finfo_close($fileInfo);

        if (!array_key_exists($mimeType, $allowedTypes)) {
            throw new Exception("Invalid file type. Only PDF, DOC, and DOCX files are allowed.");
        }

        $extension = $allowedTypes[$mimeType];
        $fileName = sprintf('%s_%s.%s', time(), bin2hex(random_bytes(4)), $extension);
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['resume']['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload resume");
        }
        
        $resumePath = $targetPath;
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

        // Attachments
        if ($resumePath) {
            $mail->addAttachment($resumePath, 'Resume_' . $fullName . '.' . pathinfo($resumePath, PATHINFO_EXTENSION));
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = "CV Submission from $fullName";

        // Build email body
        $emailBody = "<h2>New CV Submission Received</h2>";
        $emailBody .= "<p><strong>Name:</strong> $fullName</p>";
        $emailBody .= "<p><strong>Email:</strong> <a href=\"mailto:$email\">$email</a></p>";
        $emailBody .= "<p><strong>Phone:</strong> $phone</p>";
        $emailBody .= "<p><strong>Current Location:</strong> $currentLocation</p>";
        
        if ($position) {
            $emailBody .= "<p><strong>Position Interested In:</strong> $position</p>";
        }
        
        if ($portfolio) {
            $emailBody .= "<p><strong>Portfolio:</strong> <a href=\"$portfolio\" target=\"_blank\">$portfolio</a></p>";
        }

        $emailBody .= "<h3>Skills & Expertise</h3>";
        $emailBody .= "<div style=\"white-space: pre-wrap;\">" . nl2br($skills) . "</div>";

        $emailBody .= "<h3>Work Experience Summary</h3>";
        $emailBody .= "<div style=\"white-space: pre-wrap;\">" . nl2br($experience) . "</div>";

        $emailBody .= "<h3>Why they want to join</h3>";
        $emailBody .= "<div style=\"white-space: pre-wrap;\">" . nl2br($coverLetter) . "</div>";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace("<br>", "\n", $emailBody));

        $mail->send();
        
        // Clean up
        if ($resumePath && file_exists($resumePath)) {
            unlink($resumePath);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Your CV has been submitted successfully!'
        ]);
        
    } catch (Exception $e) {
        // Clean up uploaded file if error occurred
        if ($resumePath && file_exists($resumePath)) {
            unlink($resumePath);
        }
        
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