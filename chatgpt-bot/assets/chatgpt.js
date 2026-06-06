document.addEventListener('DOMContentLoaded', function () {
    const input       = document.getElementById('chatgpt-input');
    const history     = document.getElementById('chatgpt-history');
    const loading     = document.getElementById('loading-msg');
    const sendBtn     = document.getElementById('chatgpt-send');
    const suggestions = document.getElementById('chatgpt-suggestions');

    if (!input || !history || !loading) return;

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            suggestions.style.display = 'none';
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', function () {
        suggestions.style.display = 'none';
        sendMessage();
    });

    function createMessageEl(sender, content, isHtml) {
        var div    = document.createElement('div');
        var strong = document.createElement('strong');
        strong.textContent = sender + ': ';
        div.appendChild(strong);
        if (isHtml) {
            var span = document.createElement('span');
            span.className = 'chatgpt-bot-reply';
            span.innerHTML = content; // server-sanitized via wp_kses
            div.appendChild(span);
        } else {
            div.appendChild(document.createTextNode(content));
        }
        return div;
    }

    function sendMessage() {
        var msg = input.value.trim();
        if (!msg) return;

        history.appendChild(createMessageEl('You', msg, false));
        input.value = '';
        loading.style.display = 'block';

        fetch(chatgptAjax.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce':   chatgptAjax.nonce,
            },
            body: JSON.stringify({ message: msg }),
        })
        .then(function (res) {
            if (!res.ok) {
                return res.json().then(function (err) {
                    throw new Error(err.message || 'Server error.');
                });
            }
            return res.json();
        })
        .then(function (data) {
            var reply = typeof data === 'string' ? data : (data.message || 'No response.');
            history.appendChild(createMessageEl(chatgptAjax.botName || 'AI Agent', reply, true));
            history.scrollTop = history.scrollHeight;
            loading.style.display = 'none';
        })
        .catch(function (err) {
            var errEl = document.createElement('div');
            errEl.style.color = 'red';
            errEl.textContent = 'Error: ' + (err.message || 'Could not reach the chatbot. Please try again.');
            history.appendChild(errEl);
            loading.style.display = 'none';
        });
    }

    var icon = document.getElementById('chatgpt-box-icon');
    var box  = document.getElementById('chatgpt-box');

    if (icon && box) {
        icon.addEventListener('click', function () {
            if (box.classList.contains('chatgpt-open')) {
                box.classList.remove('chatgpt-open');
                setTimeout(function () { box.style.display = 'none'; }, 300);
            } else {
                box.style.display = 'block';
                // allow display:block to paint before adding transition class
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        box.classList.add('chatgpt-open');
                    });
                });
            }
        });
    }
});
