/**
 * Subscribe form handler. Delegated from the document so it works for any
 * .subscribe-form on the page (including banners injected into post bodies).
 */
(function () {
    const EMAIL_RE = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

    document.addEventListener('submit', async function (e) {
        const form = e.target.closest('.subscribe-form');
        if (!form) return;
        e.preventDefault();

        const emailInput = form.querySelector('[name="email"]');
        const honeypot = form.querySelector('[name="website"]');
        const message = form.querySelector('.subscribe-message');
        const button = form.querySelector('.subscribe-button');
        const email = (emailInput.value || '').trim();

        message.className = 'subscribe-message';

        if (!EMAIL_RE.test(email)) {
            message.textContent = 'Please enter a valid email address.';
            message.classList.add('is-error');
            return;
        }

        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Subscribing…';

        try {
            const res = await fetch('/subscribe/subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, website: honeypot ? honeypot.value : '' })
            });
            const data = await res.json();

            message.textContent = data.message || (data.success
                ? 'Thanks for subscribing!'
                : 'Something went wrong. Please try again.');
            message.classList.add(data.success ? 'is-success' : 'is-error');
            if (data.success) emailInput.value = '';
        } catch (err) {
            message.textContent = 'Network error. Please try again.';
            message.classList.add('is-error');
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    });
})();
