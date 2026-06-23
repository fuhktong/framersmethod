/**
 * Post editor image handling: featured-image upload/preview and
 * "insert image" into the Markdown body. Shared by new + edit forms.
 */
(function () {
    async function uploadImage(file) {
        const data = new FormData();
        data.append('image', file);
        const res = await fetch('/admin/upload.php', { method: 'POST', body: data });
        return res.json();
    }

    // ---- Featured image ----
    const featuredFile = document.getElementById('featured-file');
    const featuredHidden = document.getElementById('featured_image');
    const featuredPreview = document.getElementById('featured-preview');
    const featuredRemove = document.getElementById('featured-remove');

    function renderFeatured() {
        const url = featuredHidden ? featuredHidden.value : '';
        if (url) {
            featuredPreview.src = url;
            featuredPreview.style.display = 'block';
            if (featuredRemove) featuredRemove.style.display = 'inline';
        } else {
            featuredPreview.removeAttribute('src');
            featuredPreview.style.display = 'none';
            if (featuredRemove) featuredRemove.style.display = 'none';
        }
    }

    if (featuredFile && featuredHidden && featuredPreview) {
        featuredFile.addEventListener('change', async function () {
            if (!featuredFile.files.length) return;
            featuredFile.disabled = true;
            const result = await uploadImage(featuredFile.files[0]);
            featuredFile.disabled = false;
            featuredFile.value = '';
            if (result.success) {
                featuredHidden.value = result.url;
                renderFeatured();
            } else {
                alert('Upload failed: ' + result.message);
            }
        });

        if (featuredRemove) {
            featuredRemove.addEventListener('click', function () {
                featuredHidden.value = '';
                renderFeatured();
            });
        }

        renderFeatured();
    }

    // ---- Excerpt character counter ----
    const excerpt = document.getElementById('excerpt');
    const excerptCounter = document.getElementById('excerpt-counter');
    if (excerpt && excerptCounter) {
        const max = excerpt.getAttribute('maxlength') || 150;
        const updateCount = () => { excerptCounter.textContent = excerpt.value.length + '/' + max; };
        excerpt.addEventListener('input', updateCount);
        updateCount();
    }

    // ---- Insert [subscribe] shortcode into the body ----
    const bodyForSubscribe = document.getElementById('body');
    const insertSubscribe = document.getElementById('insert-subscribe');
    if (bodyForSubscribe && insertSubscribe) {
        insertSubscribe.addEventListener('click', function () {
            const snippet = '\n\n[subscribe]\n\n';
            const start = bodyForSubscribe.selectionStart;
            const end = bodyForSubscribe.selectionEnd;
            bodyForSubscribe.value = bodyForSubscribe.value.slice(0, start) + snippet + bodyForSubscribe.value.slice(end);
            bodyForSubscribe.focus();
            const caret = start + snippet.length;
            bodyForSubscribe.setSelectionRange(caret, caret);
        });
    }

    // ---- Insert image into the body ----
    const bodyEl = document.getElementById('body');
    const insertBtn = document.getElementById('insert-image');
    const insertFile = document.getElementById('insert-image-file');

    if (bodyEl && insertBtn && insertFile) {
        insertBtn.addEventListener('click', () => insertFile.click());

        insertFile.addEventListener('change', async function () {
            if (!insertFile.files.length) return;
            const originalText = insertBtn.textContent;
            insertBtn.disabled = true;
            insertBtn.textContent = 'Uploading…';

            const result = await uploadImage(insertFile.files[0]);

            insertBtn.disabled = false;
            insertBtn.textContent = originalText;
            insertFile.value = '';

            if (!result.success) {
                alert('Upload failed: ' + result.message);
                return;
            }

            const snippet = '![](' + result.url + ')';
            const start = bodyEl.selectionStart;
            const end = bodyEl.selectionEnd;
            bodyEl.value = bodyEl.value.slice(0, start) + snippet + bodyEl.value.slice(end);

            // Drop the cursor between the [] so alt text can be typed
            bodyEl.focus();
            const caret = start + 2;
            bodyEl.setSelectionRange(caret, caret);
        });
    }
})();
