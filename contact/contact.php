<?php
// Load environment variables for reCAPTCHA
require_once __DIR__ . '/env_loader.php';
loadEnv(__DIR__ . '/../../.env');

// Debug: Check if site key is loaded
$sitekey = $_ENV['RECAPTCHA_SITE_KEY'] ?? getenv('RECAPTCHA_SITE_KEY') ?? '';
if (empty($sitekey)) {
    error_log("reCAPTCHA site key not found in environment variables");
    // Fallback to hardcoded key for testing
    $sitekey = '6LfdfGIrAAAAAIgqPTUPLeyDlzTE_NADYQ-vmy4M';
}
?>

<section>
    <div class="contact-logo">
        <img src="/images/framersmethod14.png" alt="The Framers' Method" />
    </div>

    <?php include __DIR__ . '/../socialmediabar/socialmediabar.php'; ?>

    <section class="contact-card">
        <h1 class="contact-title">Send us a message</h1>

        <form id="contactForm" onsubmit="handleSubmit(event)" class="contact-form">
            <div class="contact-field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required />
            </div>

            <div class="contact-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required />
            </div>

            <div class="contact-field">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>

            <div class="g-recaptcha" data-sitekey="<?php echo $sitekey; ?>"></div>

            <button type="submit" id="submitBtn" class="btn-contact">Send</button>

            <div id="status" class="status-message" style="display: none;"></div>
        </form>
    </section>
</section>

<!-- reCAPTCHA Script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
// Contact Form Handler
async function handleSubmit(event) {
    event.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const statusDiv = document.getElementById('status');
    const form = document.getElementById('contactForm');

    // Get reCAPTCHA response
    const recaptchaResponse = grecaptcha.getResponse();

    if (!recaptchaResponse) {
        statusDiv.style.display = 'block';
        statusDiv.textContent = 'Please complete the reCAPTCHA verification';
        statusDiv.className = 'status-message error';
        return;
    }

    // Get form data
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        message: document.getElementById('message').value,
        'g-recaptcha-response': recaptchaResponse
    };

    // Update UI
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    statusDiv.style.display = 'block';
    statusDiv.textContent = 'Sending...';
    statusDiv.className = 'status-message';

    try {
        const response = await fetch('/contact/process_form.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseText = await response.text();
        let data;

        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Raw response:', responseText);
            throw new Error('Invalid JSON response from server');
        }

        if (data.success) {
            statusDiv.textContent = 'Message sent successfully!';
            statusDiv.className = 'status-message success';
            form.reset();
            grecaptcha.reset(); // Reset reCAPTCHA
        } else {
            statusDiv.textContent = data.message || 'Failed to send message';
            statusDiv.className = 'status-message error';
            grecaptcha.reset(); // Reset reCAPTCHA on error too
        }
    } catch (error) {
        console.error('Error:', error);
        statusDiv.textContent = error.message || 'An error occurred. Please try again.';
        statusDiv.className = 'status-message error';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send';
    }
}

</script>
