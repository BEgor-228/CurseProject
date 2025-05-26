document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.getElementById('content-wrapper');
    const toggleButton = document.getElementById('sidebar-toggle');
    const loginModal = document.getElementById('login-modal');
    const closeModal = document.getElementById('close-login-modal');
    const loginRoleInput = document.getElementById('login-role');
    const hallData = document.getElementById('hall-data');
    const perfTitle = hallData.dataset.title;
    const perfGenre = hallData.dataset.genre;
    const perfDuration = hallData.dataset.duration;
    const hallNumber = hallData.dataset.hall;
    const perfPrice = hallData.dataset.price;

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


    //Добавил 22:07 26.05
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

    document.querySelectorAll('.place-btn').forEach(btn => {
        const tooltip = btn.querySelector('.place-tooltip');
        btn.addEventListener('mouseenter', () => {
            tooltip.textContent = `Статус: ${btn.dataset.status}, Зона: ${btn.dataset.level}, Цена: ${btn.dataset.price} руб.`;
            tooltip.style.display = 'block';
        });
        btn.addEventListener('mouseleave', () => {
            tooltip.style.display = 'none';
        });

        btn.addEventListener('click', function() {
            if (btn.dataset.taken === '1') return; // Не реагировать на занятые

            const placeNumber = btn.dataset.place;
            const placeStatus = btn.dataset.level;
            const price = btn.dataset.price;
            const total = Number(perfPrice) + Number(price);

            document.getElementById('buy-modal-content').innerHTML = `
                <div style="font-size:1.1rem;">
                    <b>Название спектакля:</b> ${perfTitle}<br>
                    <b>Жанр:</b> ${perfGenre}<br>
                    <b>Длительность:</b> ${perfDuration}<br>
                    <b>Зал №:</b> ${hallNumber}<br>
                    <b>Место №:</b> ${placeNumber} (${placeStatus})<br>
                    <b>Цена спектакля:</b> ${perfPrice} руб.<br>
                    <b>Цена места:</b> ${price} руб.<br>
                    <b style="color:#facc15;">Общая цена: ${total} руб.</b><br>
                    <button style="margin-top:12px;background:#facc15;color:#222;font-weight:bold;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;">Купить</button>
                </div>
            `;
            document.getElementById('buy-modal').style.display = 'block';
        });
    });

    document.getElementById('buy-modal-close').onclick = function() {
        document.getElementById('buy-modal').style.display = 'none';
    };
});