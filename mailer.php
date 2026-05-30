<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

// ── CONFIGURATION ─────────────────────────────────────────────
define('MAIL_HOST', 'smtp.gmail.com');

// ── CONFIGURATION ──────────────────────────────────────────────
// Change ces valeurs par tes vraies infos Gmail
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_USERNAME', 'tonmail@gmail.com');     // ← ton email Gmail
define('MAIL_PASSWORD', 'qhnq psqp jqge kncr');   // ← mot de passe d'application Gmail (pas ton vrai mdp)
define('MAIL_PORT',     587);
define('SHOP_NAME',     'Kyutopia');
define('SHOP_EMAIL',    'tonmail@gmail.com');      // ← email où tu reçois les notifications

// ── FONCTION PRINCIPALE D'ENVOI ────────────────────────────────
function sendEmail($to, $toName, $subject, $htmlBody) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(SHOP_EMAIL, SHOP_NAME);
        $mail->addAddress($to, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        $mail->send();
        
        // ✅ Log succès
        error_log("Email envoyé avec succès à : " . $to);
        return true;

    } catch (Exception $e) {
        // ✅ Log erreur détaillée
        error_log("ERREUR EMAIL : " . $mail->ErrorInfo);
        
        // ✅ Afficher l'erreur directement à l'écran pour débugger
        die("Erreur email : " . $mail->ErrorInfo);
        
        return false;
    }
}

// ── EMAIL 1 : CONFIRMATION AU CLIENT ──────────────────────────
function sendOrderConfirmation($order) {

    $subject = "✅ Commande reçue - Kyutopia";

    $html = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;border:1px solid #eee;border-radius:12px;overflow:hidden'>

        <!-- Header -->
        <div style='background:#e91e63;padding:30px;text-align:center'>
            <h1 style='color:white;margin:0'>Kyutopia 🌸</h1>
        </div>

        <!-- Body -->
        <div style='padding:30px'>
            <h2 style='color:#e91e63'>Merci pour ta commande, {$order['first_name']} ! 💗</h2>

            <p>Ta commande a bien été reçue. On va te contacter bientôt pour confirmer les détails.</p>

            <!-- Récapitulatif -->
            <div style='background:#fce4ec;border-radius:8px;padding:16px;margin:20px 0'>
                <h3 style='margin-top:0;color:#333'>📦 Récapitulatif</h3>
                <p><b>Produits :</b> {$order['product']}</p>
                <p><b>Quantité totale :</b> {$order['quantity']} article(s)</p>
                <p><b>Téléphone :</b> {$order['phone']}</p>
            </div>

            <p>Pour toute question, contacte-nous :</p>

            <a href='https://www.instagram.com/kyutopia__'
               style='display:inline-block;background:#e91e63;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;margin-right:10px'>
               Instagram
            </a>

            <a href='https://wa.me/+21654461357'
               style='display:inline-block;background:#25D366;color:white;padding:10px 20px;border-radius:6px;text-decoration:none'>
               WhatsApp
            </a>
        </div>

        <!-- Footer -->
        <div style='background:#f9f9f9;padding:16px;text-align:center;font-size:12px;color:#aaa'>
            © Kyutopia — Handmade with love 🌸
        </div>

    </div>
    ";

    return sendEmail(
        $order['email'],
        $order['first_name'] . ' ' . $order['last_name'],
        $subject,
        $html
    );
}

// ── EMAIL 2 : NOTIFICATION À TOI (KYUTOPIA) ───────────────────
function sendAdminNotification($order) {

    $subject = "🛍️ Nouvelle commande de {$order['first_name']} {$order['last_name']}";
    
    // ✅ On calcule la valeur avant de l'injecter dans la string
    $notes = !empty($order['notes']) ? $order['notes'] : '—';

    $html = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto'>

        <h2 style='color:#e91e63'>Nouvelle commande reçue !</h2>

        <table style='width:100%;border-collapse:collapse'>
            <tr style='background:#fce4ec'>
                <td style='padding:10px;border:1px solid #eee'><b>Nom</b></td>
                <td style='padding:10px;border:1px solid #eee'>{$order['first_name']} {$order['last_name']}</td>
            </tr>
            <tr>
                <td style='padding:10px;border:1px solid #eee'><b>Email</b></td>
                <td style='padding:10px;border:1px solid #eee'>{$order['email']}</td>
            </tr>
            <tr style='background:#fce4ec'>
                <td style='padding:10px;border:1px solid #eee'><b>Téléphone</b></td>
                <td style='padding:10px;border:1px solid #eee'>{$order['phone']}</td>
            </tr>
            <tr>
                <td style='padding:10px;border:1px solid #eee'><b>Produits</b></td>
                <td style='padding:10px;border:1px solid #eee'>{$order['product']}</td>
            </tr>
            <tr style='background:#fce4ec'>
                <td style='padding:10px;border:1px solid #eee'><b>Quantité</b></td>
                <td style='padding:10px;border:1px solid #eee'>{$order['quantity']}</td>
            </tr>
            <tr>
                <td style='padding:10px;border:1px solid #eee'><b>Notes</b></td>
                <td style='padding:10px;border:1px solid #eee'>$notes</td>
            </tr>
        </table>

        <br>
        <a href='http://localhost/kyutopia/admin.php'
           style='background:#e91e63;color:white;padding:12px 24px;border-radius:6px;text-decoration:none'>
           Voir dans l'admin
        </a>
    </div>
    ";

    return sendEmail(SHOP_EMAIL, SHOP_NAME, $subject, $html);
}
?>