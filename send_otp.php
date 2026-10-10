<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$voter_id = $data['voter_id'] ?? null;

if (!$voter_id) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Voter ID required']));
}

try {
    // Get voter's contact info
    $stmt = $conn->prepare("SELECT phone, email FROM users WHERE voter_id = ?");
    $stmt->execute([$voter_id]);
    $voter = $stmt->fetch();

    if (!$voter) {
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'Voter not found']));
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Store OTP in database
    $stmt = $conn->prepare("INSERT INTO otp_verification (voter_id, otp, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$voter_id, $otp, $expires_at]);

    // Send OTP via both channels
    $sendResults = [
        'email_sent' => false,
        'sms_sent' => false
    ];

    // Send via Email if address exists
    if (!empty($voter['email'])) {
        $sendResults['email_sent'] = sendOtpViaEmail($voter['email'], $otp);
    }

    // Send via SMS if phone exists
    if (!empty($voter['phone'])) {
        $sendResults['sms_sent'] = sendOtpViaSms($voter['phone'], $otp);
    }

    // If neither channel worked
    if (!$sendResults['email_sent'] && !$sendResults['sms_sent']) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'OTP generated but could not be delivered',
            'otp' => $otp // Only include in development!
        ]));
    }

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully',
        'via' => array_keys(array_filter($sendResults))
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

function sendOtpViaEmail($email, $otp) {
    // Configure PHPMailer in config.php
    require_once '../includes/phpmailer/PHPMailer.php';
    require_once '../includes/phpmailer/SMTP.php';
    require_once '../includes/phpmailer/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom('election@karnataka.gov.in', 'Karnataka Election Commission');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Online Voting';
        $mail->Body    = "Your OTP is: <b>$otp</b><br>Valid for 10 minutes";
        $mail->AltBody = "Your OTP is: $otp (valid for 10 minutes)";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

function sendOtpViaSms($phone, $otp) {
    // Using Twilio API (configure in config.php)
    if (!defined('TWILIO_SID') || !defined('TWILIO_TOKEN') || !defined('TWILIO_NUMBER')) {
        return false;
    }

    $client = new Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);

    try {
        $client->messages->create(
            // Phone number
            $phone,
            [
                'from' => TWILIO_NUMBER,
                'body' => "Your voting OTP is: $otp. Valid for 10 minutes."
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("SMS sending failed: " . $e->getMessage());
        return false;
    }
}
?>