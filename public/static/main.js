let content = document.querySelector("#content");
let sidebar = document.querySelector("#sidebar");
let display = document.querySelector("#display");

function InitSession() {
    Service.sessionExist(true, (response) => {
        if (response.session === true) {
            content.style.display = 'flex';
        } else {
            content.style.display = 'none';
            openModal('modalLogin');
        }
    });
}

function Login(form) {
    event.preventDefault();
    let data = $(form).serializeArray();
    $(form).trigger("reset");
    closeModal('modalLogin')
    lockScreen()

    Service.login(
        data[0].value, data[1].value, true,
        (response) => {
            unlockScreen()
            showNotification('Login', 'Successfully')
            InitSession()
        },
        (response) => {
            unlockScreen()
            openModal('modalLogin')
        }
    )
}

function Logout() {
    lockScreen()
    Service.logout(true,
        (response) => {
            unlockScreen()
            showNotification('Logout', 'Successfully')
            InitSession()
        },
        (response) => {
            unlockScreen()
        }
    )
}

function serviceStart() {
    lockScreen();
    Service.serviceStart(true, (response) => {
        showNotification("Start Service", "Service start command success");
        setTimeout(() => {
            unlockScreen();
            selectService();
        }, 1000);
    }, (response) => {
        unlockScreen();
    })
}

function serviceStop() {
    lockScreen();
    Service.serviceStop(true, (response) => {
        showNotification("Stop Service", "Service stop command success");
        setTimeout(() => {
            unlockScreen();
            selectService();
        }, 1000);
    }, (response) => {
        unlockScreen();
    })
}

$(document).ready(function () {
    InitSession()
});