<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

require "./vendor/phpmailer/phpmailer/src/PHPMailer.php";
require "./vendor/phpmailer/phpmailer/src/Exception.php";
require "./vendor/phpmailer/phpmailer/src/SMTP.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection details
$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$database = $_ENV['DB_DATABASE'];

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array(
    'status' => 0,
    'message' => 'Failed'
);

$errorEmpty = false;
$errorEmail = false;

if (isset($_POST['name']) || isset($_POST['firstname']) || isset($_POST['email']) || isset($_POST['phone']) || isset($_POST['amount'])) {

    $name = $_POST['name'];
    $firstname = $_POST['firstname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];
    $countTicket = $_POST['amount'] / 10;
    // Generate a random unique and complex number
    /**
     * @var null|array $confirmationNumber
     */
    $confirmationNumber = [];
    //$confirmationNumber = [generateConfirmationNumber($conn)];

    for ($i = 0; $i < $countTicket; $i++) {
        array_push($confirmationNumber, generateConfirmationNumber($conn));
    }

    $tombolaTickets = implode(',', $confirmationNumber);
    $ticketsWithSpaces = str_replace(',', ', ', $tombolaTickets);
    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO participants (nom, prenom, email, montant, tombola_tickets, telephone ) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $firstname, $email, $amount, $ticketsWithSpaces, $phone);
    $stmt->execute();
    if ($stmt->error) {
        echo "Database Error: " . $stmt->error;
    }

    try {
        // Attempt to create a new instance of the PHPMailer class
        $mail = new PHPMailer(true);

        // Configure SMTP settings for sending the confirmation email
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->Port = $_ENV['EMAIL_PORT'];
        //$mail->SMTPAutoTLS = false;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = $_ENV['EMAIL_USERNAME'];;
        $mail->Password = $_ENV['EMAIL_PSW'];
        $mail->CharSet = 'UTF-8';
        $mail->ContentType = 'text/plain; charset=UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom('contact@curagreen.fr', "L'équipe CuraGreen");
        $mail->addAddress($email, ucfirst(strtolower($firstname)) . ' ' .ucfirst(strtolower($name)));
        $mail->Subject = 'Confirmation de participation';
        
        // HTML body with logo
        $logoUrl = 'https://curagreen.fr/wp-content/uploads/Logo-Version-horizontale-e1682347436851.png'; // Replace with the URL of your logo image
        $body = '<html><head>';
        $body .= '<style>';
        $body .= 'body { font-family: Ranade; }';
        $body .= '</style>';
        $body .= '</head><body>';
        $body .= '<a href="https://curagreen.fr/" target="_blank"><img src="' . $logoUrl . '" alt="Logo" width="50%"></a>';
        $body .= '<p>Bonjour ' . ucfirst(strtolower($firstname)) . ' ' . ucfirst(strtolower($name)) . ',</p>';
        $body .= '<p>Nous vous remercions pour votre participation !</p>';
        if (strlen($ticketsWithSpaces) < 11) {
            $body .= '<p>Voici votre numéro de ticket : ' . $ticketsWithSpaces . '.</p>';
        }
        else {
            $body .= '<p>Voici vos ' . $countTicket . ' numéros de ticket : ' . $ticketsWithSpaces . '.</p>';
        }
        $body .= '<p>Bonne chance !</p>';
        $body .= "<p>L'équipe CuraGreen.</p>";
        $body .= '</body></html>';

        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->send();

        $response['message'] = "Participation enregistrée !";
        echo json_encode($response);
    } catch (Exception $e) {
        
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}

function generateConfirmationNumber($conn)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $length = 5;
    $randomString = '';

    $isUnique = false;

    while (!$isUnique) {
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Check if the generated number already exists in the database
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM participants WHERE FIND_IN_SET(?, tombola_tickets)");
        $stmt->bind_param("s", $randomString);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $isUnique = true;
        }
    }

    return $randomString;
}
