document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.getElementById('content-wrapper');
    const toggleButton = document.getElementById('sidebar-toggle');
    const loginModal = document.getElementById('login-modal');
    const closeModal = document.getElementById('close-login-modal');
    const loginRoleInput = document.getElementById('login-role');

    // Sidebar toggle
    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('closed');
            contentWrapper.classList.toggle('closed');
            toggleButton.textContent = sidebar.classList.contains('closed') ? '☰' : '✕';
        });
    }

    // Login modal
    if (loginModal && closeModal && loginRoleInput) {
        document.querySelectorAll('.dropdown-link, .login-mobile').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const role = this.getAttribute('data-role');
                loginRoleInput.value = role;
                loginModal.style.display = 'block';
            });
        });

        closeModal.addEventListener('click', () => {
            loginModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === loginModal) {
                loginModal.style.display = 'none';
            }
        });
    }

    // Показывать popup при попытке купить билет без авторизации
    document.querySelectorAll('.show-login-popup').forEach(btn => {
        btn.addEventListener('click', function() {
            const popup = document.getElementById('login-required-popup');
            if (popup) {
                popup.style.display = 'block';
                setTimeout(() => {
                    popup.style.display = 'none';
                }, 3000);
            }
        });
    });
});