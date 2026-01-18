// File: includes/email.php
<?php
function sendEmail($to, $subject, $body) {
    $headers = "From: noreply@campusnest.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $body, $headers);
}

// Booking confirmation email
function sendBookingConfirmation($user_email, $booking_details) {
    $subject = "Booking Confirmation - Campus Nest";
    $body = "<h2>Your booking has been confirmed!</h2>
             <p>Booking ID: {$booking_details['booking_id']}</p>
             <p>Room: {$booking_details['room_number']}</p>";
    return sendEmail($user_email, $subject, $body);
}
?>
