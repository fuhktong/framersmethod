/**
 * Tag/category chip input.
 * Turns a hidden <input id="category"> (comma-separated) into editable chips.
 * Expects: #tag-chips (chip container, above), #tag-input (text box),
 * #category (hidden). Chips render in their own row so the input keeps a
 * consistent size. Delimiters: Enter, Tab, or comma — NOT space.
 */
(function () {
    const chipsBox = document.getElementById('tag-chips');
    const input = document.getElementById('tag-input');
    const hidden = document.getElementById('category');
    if (!chipsBox || !input || !hidden) return;

    let tags = (hidden.value || '')
        .split(',')
        .map(t => t.trim())
        .filter(Boolean);

    function render() {
        chipsBox.innerHTML = '';
        tags.forEach((tag, index) => {
            const chip = document.createElement('span');
            chip.className = 'tag-chip';
            chip.textContent = tag;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'tag-chip-remove';
            remove.setAttribute('aria-label', 'Remove ' + tag);
            remove.textContent = '×';
            remove.addEventListener('click', function () {
                tags.splice(index, 1);
                render();
            });

            chip.appendChild(remove);
            chipsBox.appendChild(chip);
        });
        hidden.value = tags.join(', ');
    }

    function addTag(value) {
        const tag = value.replace(/,/g, '').trim();
        if (tag && !tags.some(t => t.toLowerCase() === tag.toLowerCase())) {
            tags.push(tag);
            render();
        }
    }

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',' || (e.key === 'Tab' && input.value.trim())) {
            e.preventDefault();
            addTag(input.value);
            input.value = '';
        } else if (e.key === 'Backspace' && !input.value && tags.length) {
            tags.pop();
            render();
        }
    });

    // Capture a typed-but-unconfirmed tag when leaving the field or submitting
    input.addEventListener('blur', function () {
        if (input.value.trim()) { addTag(input.value); input.value = ''; }
    });

    const form = field.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            if (input.value.trim()) { addTag(input.value); input.value = ''; }
        });
    }

    render();
})();
