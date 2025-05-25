document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.getElementById('content-wrapper');
    const toggleButton = document.getElementById('sidebar-toggle');

    toggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        contentWrapper.classList.toggle('collapsed');
        toggleButton.textContent = sidebar.classList.contains('collapsed') ? '☰' : '✕';
    });
});
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dropdown-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            let role = '';
            if (this.textContent.includes('пользователя')) role = 'viewer';
            if (this.textContent.includes('Менеджера')) role = 'manager';
            if (this.textContent.includes('Администратора')) role = 'admin';
            document.getElementById('login-role').value = role;
            document.getElementById('login-modal').style.display = 'block';
        });
    });
    document.getElementById('close-login-modal').onclick = function() {
        document.getElementById('login-modal').style.display = 'none';
    };
    window.onclick = function(event) {
        if (event.target == document.getElementById('login-modal')) {
            document.getElementById('login-modal').style.display = 'none';
        }
    };
});