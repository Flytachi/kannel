function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function lockScreen() {
    document.getElementById('overlay').style.display = 'block';
    let loader = document.createElement('div');
    loader.id = 'overlayLoader'
    loader.style.top = '0';
    loader.style.left = '0';
    loader.style.width = '100%';
    loader.style.height = '100%';
    loader.style.display = 'flex';
    loader.style.justifyContent = 'center';
    loader.style.alignItems = 'center';
    loader.style.color = 'white';
    loader.innerText = 'Loading...';
    document.getElementById('overlay').appendChild(loader);
}

function unlockScreen() {
    document.getElementById('overlay').style.display = 'none';
    let overlay = document.getElementById('overlayLoader');
    if (overlay) {
        overlay.remove();
    }
}

function showNotification(title, message, iconType = 'info') {
    let icon;
    if (iconType === 'error') icon = '❌';
    else if (iconType === 'warning') icon = '⚠';
    else icon = '🛈';
    const container = document.getElementById('notification-container');

    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = 'notification';

    notification.innerHTML = `
            <div class="notification-icon">${icon}</div>
            <div class="notification-content">
                <div class="notification-title">${title}</div>
                <div class="notification-text">${message}</div>
            </div>
            <div class="notification-close" onclick="this.parentElement.remove()">✕</div>
        `;
    container.appendChild(notification);
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.sidebar-menu li').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.sidebar-menu li').forEach(el => el.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});