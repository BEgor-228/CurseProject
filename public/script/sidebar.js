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

    // Тултипы для мест
    document.querySelectorAll('.place-btn').forEach(btn => {
        const tooltip = btn.querySelector('.place-tooltip');
        btn.addEventListener('mouseenter', () => {
            tooltip.textContent = `Статус: ${btn.dataset.status}, Зона: ${btn.dataset.level}, Цена: ${btn.dataset.price} руб.`;
            tooltip.style.display = 'block';
        });
        btn.addEventListener('mouseleave', () => {
            tooltip.style.display = 'none';
        });
    });

    //дял генерации отчетов
    document.getElementById('least_period').addEventListener('change', function() {
        document.getElementById('pdf_least_period').value = this.value;
    });
    document.getElementById('genre').addEventListener('change', function() {
        document.getElementById('pdf_genre').value = this.value;
    });
    document.getElementById('hall').addEventListener('change', function() {
        document.getElementById('pdf_hall').value = this.value;
    });
    document.getElementById('repertoire_profit').addEventListener('change', function() {
        document.getElementById('pdf_repertoire_profit').value = this.value;
    });
    document.getElementById('repertoire_perf').addEventListener('change', function() {
        document.getElementById('pdf_repertoire_perf').value = this.value;
    });

    ['least_period', 'genre', 'hall', 'repertoire_profit', 'repertoire_perf'].forEach(function(field) {
        document.getElementById(field).addEventListener('change', function() {
            document.getElementById('pdf_' + field).value = this.value;
            document.getElementById('excel_' + field).value = this.value;
            document.getElementById('word_' + field).value = this.value;
        });
    });
});