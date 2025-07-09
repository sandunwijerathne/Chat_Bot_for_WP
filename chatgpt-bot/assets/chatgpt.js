document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('chatgpt-input');
    const history = document.getElementById('chatgpt-history');
    const loading = document.getElementById('loading-msg');

    if (!input || !history || !loading) return;

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            const msg = input.value.trim();
            if (!msg) return;

            history.innerHTML += `<div><strong>You:</strong> ${msg}</div>`;
            input.value = '';
            loading.style.display = 'block';

            fetch(chatgptAjax.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            })
                .then(res => res.json())
                .then(data => {
                   history.innerHTML += `<div><strong>Bot:</strong> <span class="chatgpt-bot-reply">${data}</span></div>`;

                    history.scrollTop = history.scrollHeight;
                    loading.style.display = 'none';
                })
                .catch(() => {
                    history.innerHTML += `<div style="color:red;">Error talking to ChatGPT</div>`;
                    loading.style.display = 'none';
                });
        }
    });
    
    
    
      const icon = document.getElementById('chatgpt-box-icon');
    const box = document.getElementById('chatgpt-box');
    
    // Ensure elements exist
    if (icon && box) {
        // Initially hide the box (if needed)
        box.style.display = 'none';
        
        icon.addEventListener('click', function() {
            if (box.style.display === 'none' || box.style.display === '') {
                // Slide down
                box.style.display = 'block';
                box.style.height = '0';
                box.style.overflow = 'hidden';
                
                // Trigger animation
                setTimeout(() => {
                    box.style.height = box.scrollHeight + 'px';
                }, 10);
                
                // Remove height after animation completes
                setTimeout(() => {
                    box.style.height = '';
                }, 300);
            } else {
                // Slide up
                box.style.height = box.scrollHeight + 'px';
                box.style.overflow = 'hidden';
                
                // Trigger animation
                setTimeout(() => {
                    box.style.height = '0';
                }, 10);
                
                // Hide after animation completes
                setTimeout(() => {
                    box.style.display = 'none';
                    box.style.height = '';
                }, 300);
            }
        });
    }
    
    
    
});
