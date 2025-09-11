<?php
$active = "Contact";
include('db.php');
include("functions.php");
include("header.php");
?>

<head>
    <link rel="stylesheet" href="css/contactus.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<!-- Contact Section Begin -->
<section class="edu-contact-section">
    <div class="container">
        <?php
        // Language-based contact section translations
        $contact_texts = [
            'en' => [
                'title' => 'Contact Us',
                'subtitle' => 'Reach out to us through any of these platforms',
                'call' => 'Call Us',
                'call_now' => 'Call Now',
                'whatsapp' => 'WhatsApp',
                'chat_now' => 'Chat Now',
                'facebook' => 'Facebook',
                'instagram' => 'Instagram',
                'tiktok' => 'TikTok',
                'youtube' => 'YouTube',
                'linkedin' => 'LinkedIn',
                'see_profile' => 'See Profile',
                'leave_message' => 'Leave A Message',
                'staff_callback' => 'Our staff will call back later and answer your questions.',
                'your_name' => 'Your name',
                'your_email' => 'Your email',
                'message_subject' => 'Message Subject',
                'your_message' => 'Your message',
                'send_message' => 'Send message'
            ],
            'si' => [
                'title' => 'අප අමතන්න',
                'subtitle' => 'මේ ඕනෑම මාධ්‍යයක් හරහා අප අමතන්න',
                'call' => 'අපට කතා කරන්න',
                'call_now' => 'දැන් කතා කරන්න',
                'whatsapp' => 'වට්ස්ඇප්',
                'chat_now' => 'දැන් කතාබහ කරන්න',
                'facebook' => 'ෆේස්බුක්',
                'instagram' => 'ඉන්ස්ටග්‍රෑම්',
                'tiktok' => 'ටික්ටොක්',
                'youtube' => 'යූටියුබ්',
                'linkedin' => 'ලින්ක්ඩ්ඉන්',
                'see_profile' => 'පැතිකඩ බලන්න',
                'leave_message' => 'පණිවුඩයක් තබන්න',
                'staff_callback' => 'අපගේ කාර්ය මණ්ඩලය පසුව ඔබට කතා කර ඔබගේ ප්‍රශ්න වලට පිළිතුරු දෙනු ඇත.',
                'your_name' => 'ඔබේ නම',
                'your_email' => 'ඔබේ විද්‍යුත් තැපෑල',
                'message_subject' => 'පණිවිඩයේ මාතෘකාව',
                'your_message' => 'ඔබේ පණිවිඩය',
                'send_message' => 'පණිවිඩය යවන්න'
            ]
        ];
        ?>
        <div class="row">
            <div class="col-lg-5">
                <div class="edu-contact-info-box">
                    <h2 class="edu-contact-info-title"><?php echo $contact_texts[$lang]['title']; ?></h2>
                    <p class="edu-contact-subtitle"><?php echo $contact_texts[$lang]['subtitle']; ?></p>
                    
                    <div class="edu-contact-links-wrapper">
                        <div class="edu-contact-item edu-contact-call">
                            <span class="edu-contact-item-label"><?php echo $contact_texts[$lang]['call']; ?></span>
                            <a href="tel:+94777138134" class="edu-contact-button">
                                <i class="fas fa-phone-alt"></i>
                                <?php echo $contact_texts[$lang]['call_now']; ?>
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-whatsapp">
                            <span class="edu-contact-item-label"><?php echo $contact_texts[$lang]['whatsapp']; ?></span>
                            <a href="https://wa.me/+94777138134" class="edu-contact-button">
                                <i class="fab fa-whatsapp"></i>
                                <?php echo $contact_texts[$lang]['chat_now']; ?>
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-facebook">
                            <span class="edu-contact-item-label"><?php echo $contact_texts[$lang]['facebook']; ?></span>
                            <a href="https://www.facebook.com/edutourism.lk/" class="edu-contact-button">
                                <i class="fab fa-facebook-f"></i>
                                <?php echo $contact_texts[$lang]['see_profile']; ?>
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-instagram">
                            <span class="edu-contact-item-label"><?php echo $contact_texts[$lang]['instagram']; ?></span>
                            <a href="https://www.instagram.com/edutourism.lk/" class="edu-contact-button">
                                <i class="fab fa-instagram"></i>
                                <?php echo $contact_texts[$lang]['see_profile']; ?>
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-tiktok">
                            <span class="edu-contact-item-label"><?php echo $contact_texts[$lang]['tiktok']; ?></span>
                            <a href="https://www.tiktok.com/@edutourism.lk" class="edu-contact-button">
                                <i class="fab fa-tiktok"></i>
                                <?php echo $contact_texts[$lang]['see_profile']; ?>
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-youtube">
                            <span class="edu-contact-item-label"><?php echo $contact_texts[$lang]['youtube']; ?></span>
                            <a href="https://youtube.com/@edutourismLK" class="edu-contact-button">
                                <i class="fab fa-youtube"></i>
                                <?php echo $contact_texts[$lang]['see_profile']; ?>
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-linkedin">
                            <span class="edu-contact-item-label"><?php echo $contact_texts[$lang]['linkedin']; ?></span>
                            <a href="https://www.linkedin.com/in/edutourism/" class="edu-contact-button">
                                <i class="fab fa-linkedin-in"></i>
                                <?php echo $contact_texts[$lang]['see_profile']; ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="edu-contact-map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.271729174325!2d79.9266075099594!3d6.977231617696542!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae257c1737d6de5%3A0xa329403f5a6cf7e6!2sEduTourism%20Pvt%20Ltd!5e0!3m2!1sen!2slk!4v1746771913343!5m2!1sen!2slk" 
                                width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="edu-contact-form-container">
                    <h4 class="edu-contact-form-title"><?php echo $contact_texts[$lang]['leave_message']; ?></h4>
                    <p class="edu-contact-form-subtitle"><?php echo $contact_texts[$lang]['staff_callback']; ?></p>
                    
                    <form action="contact.php" method="post" class="edu-contact-form">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="edu-form-group">
                                    <input type="text" placeholder="<?php echo $contact_texts[$lang]['your_name']; ?>" class="edu-form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="edu-form-group">
                                    <input type="email" placeholder="<?php echo $contact_texts[$lang]['your_email']; ?>" class="edu-form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="edu-form-group">
                                    <input type="text" placeholder="<?php echo $contact_texts[$lang]['message_subject']; ?>" class="edu-form-control" name="subject" required>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="edu-form-group">
                                    <textarea placeholder="<?php echo $contact_texts[$lang]['your_message']; ?>" class="edu-form-control" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="edu-send-btn" name="submit">
                                    <i class="fas fa-paper-plane"></i> <?php echo $contact_texts[$lang]['send_message']; ?>
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php
                    if (isset($_POST['submit'])) {
                        $user_name = $_POST['name'];
                        $user_email = $_POST['email'];
                        $user_subject = $_POST['subject'];
                        $user_msg = $_POST['message'];

                        // Email details
                        $to = 'info@edutourism.lk';
                        $headers = "From: $user_email" . "\r\n" .
                                  "Reply-To: $user_email" . "\r\n" .
                                  "X-Mailer: PHP/" . phpversion();
                        
                        // Send email
                        $mail_sent = mail($to, $user_subject, $user_msg, $headers);
                        
                        // Show success/error message
                        if ($mail_sent) {
                            echo "<div class='edu-alert edu-alert-success'>Your message has been sent successfully!</div>";
                        } else {
                            echo "<div class='edu-alert edu-alert-danger'>Sorry, there was an error sending your message.</div>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Contact Section End -->

<?php include('footer.php'); ?>