document.addEventListener('DOMContentLoaded', function () {

        var slides = document.querySelectorAll('.slide');
    if (slides.length > 1) {
        var current = 0;
        var showSlide = function (step) {
            slides[current].classList.remove('on');
            current = (current + step + slides.length) % slides.length;
            slides[current].classList.add('on');
        };
        var timer = setInterval(function () { showSlide(1); }, 5000);
        document.querySelectorAll('[data-slide]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showSlide(parseInt(btn.dataset.slide, 10));
                clearInterval(timer);
                timer = setInterval(function () { showSlide(1); }, 5000);
            });
        });
    }

        document.querySelectorAll('[data-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function (ev) {
            ev.stopPropagation();
            var target = document.getElementById(btn.dataset.toggle);
            if (target) { target.classList.toggle('open'); }
        });
    });
    document.addEventListener('click', function () {
        var dd = document.getElementById('user-dropdown');
        if (dd) { dd.classList.remove('open'); }
    });

        document.querySelectorAll('.alert').forEach(function (alert) {
        var close = alert.querySelector('.alert-close');
        if (close) { close.addEventListener('click', function () { alert.remove(); }); }
        if (alert.classList.contains('alert-success')) {
            setTimeout(function () { alert.classList.add('fade'); }, 4000);
            setTimeout(function () { alert.remove(); }, 4600);
        }
    });

        document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            if (!confirm(form.dataset.confirm)) { ev.preventDefault(); }
        });
    });

        document.querySelectorAll('[data-show]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.show);
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.textContent = input.type === 'password' ? 'Show' : 'Hide';
        });
    });

        document.querySelectorAll('[data-counter]').forEach(function (field) {
        var counter = document.createElement('small');
        counter.className = 'counter';
        field.insertAdjacentElement('afterend', counter);
        var update = function () {
            counter.textContent = field.value.length + ' / ' + field.getAttribute('maxlength');
        };
        field.addEventListener('input', update);
        update();
    });

        document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
        var img = document.getElementById(input.dataset.preview);
        input.addEventListener('change', function () {
            if (input.files && input.files[0] && img) {
                img.src = URL.createObjectURL(input.files[0]);
                img.hidden = false;
            }
        });
    });

        function fieldError(field) {
        var v = field.value.trim();
        var label = field.labels && field.labels[0] ? field.labels[0].textContent.replace('*', '').trim() : 'This field';
        if (field.required && v === '') { return label + ' is required.'; }
        if (v === '') { return ''; }
        if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { return 'Enter a valid email address.'; }
        if (field.minLength > 0 && v.length < field.minLength) { return label + ' must be at least ' + field.minLength + ' characters.'; }
        if (field.pattern && !new RegExp('^(?:' + field.pattern + ')$').test(v)) { return field.dataset.msg || 'Invalid format.'; }
        if (field.dataset.match) {
            var other = document.getElementById(field.dataset.match);
            if (other && other.value !== field.value) { return 'Passwords do not match.'; }
        }
        if (field.type === 'file' && field.files[0] && field.files[0].size > 2 * 1024 * 1024) { return 'Image must be 2 MB or smaller.'; }
        return '';
    }

    function showError(field, message) {
        var box = field.parentElement.querySelector('.js-error');
        if (!box) {
            box = document.createElement('small');
            box.className = 'field-error js-error';
            field.parentElement.appendChild(box);
        }
        box.textContent = message;
        field.classList.toggle('invalid', message !== '');
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        var fields = form.querySelectorAll('input:not([type=hidden]), textarea, select');
        fields.forEach(function (f) {
            f.addEventListener('blur', function () { showError(f, fieldError(f)); });
            f.addEventListener('input', function () {
                if (f.classList.contains('invalid')) { showError(f, fieldError(f)); }
            });
        });
        form.addEventListener('submit', function (ev) {
            var firstBad = null;
            fields.forEach(function (f) {
                var msg = fieldError(f);
                showError(f, msg);
                if (msg && !firstBad) { firstBad = f; }
            });
            if (firstBad) { ev.preventDefault(); firstBad.focus(); }
        });
    });

        document.querySelectorAll('[data-share]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (navigator.share) {
                navigator.share({ title: document.title, url: location.href });
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(location.href).then(function () { btn.textContent = 'Link copied!'; });
            }
        });
    });

        document.querySelectorAll('form.bookmark-form').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'fetch' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.ok) { window.location = form.action; return; }
                    var btn = form.querySelector('button');
                    btn.classList.toggle('saved', data.saved);
                    btn.textContent = data.saved ? '★ Saved' : '☆ Save';
                })
                .catch(function () { form.submit(); });
        });
    });
});
