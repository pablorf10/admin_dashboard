/*
Autor: Pablo Ríos Furniet
Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
Este archivo forma parte de un proyecto con licencia no comercial.
Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
Queda prohibido el uso comercial sin autorización expresa.
Más información: https://creativecommons.org/licenses/by-nc/4.0/
*/

const sideLinks = document.querySelectorAll('.sidebar .side-menu li a:not(.logout)');

sideLinks.forEach(item => {
    const li = item.parentElement;
    item.addEventListener('click', () => {
        sideLinks.forEach(i => {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active');
    })
});

const menuBar = document.querySelector('.content nav .bx.bx-menu');
const sideBar = document.querySelector('.sidebar');

menuBar.addEventListener('click', () => {
    sideBar.classList.toggle('close');
});

const searchBtn = document.querySelector('.content nav form .form-input button');
const searchBtnIcon = document.querySelector('.content nav form .form-input button .bx');
const searchForm = document.querySelector('.content nav form');

searchBtn.addEventListener('click', function (e) {
    if (window.innerWidth < 576) {
        e.preventDefault;
        searchForm.classList.toggle('show');
        if (searchForm.classList.contains('show')) {
            searchBtnIcon.classList.replace('bx-search', 'bx-x');
        } else {
            searchBtnIcon.classList.replace('bx-x', 'bx-search');
        }
    }
});

window.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth < 768) {
        sideBar.classList.add('close');
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth < 768) {
        sideBar.classList.add('close');
    } else {
        sideBar.classList.remove('close');
    }
    if (window.innerWidth > 576) {
        searchBtnIcon.classList.replace('bx-x', 'bx-search');
        searchForm.classList.remove('show');
    }
});

document.getElementById('notifToggle').addEventListener('click', function () {
    var dropdown = document.getElementById('notifDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});

function borrarNotificacion(id) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", BASE_URL + "/actions/delete/notificacion.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            var notifItem = document.getElementById('notif-' + id);
            if (notifItem) {
                notifItem.remove();
                actualizarContadorNotificaciones();
                comprobarNotificacionesVacias();
            }
        } else {
            alert("Error al borrar la notificación.");
        }
    };
    xhr.send("id=" + id);
}

function borrarTodasNotificaciones() {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", BASE_URL + "/actions/delete/notificaciones.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            var notifDropdown = document.getElementById('notifDropdown');
            notifDropdown.innerHTML = '<div class="notif-item no-notifications"><p class="text-center">No hay notificaciones.</p></div>';
            actualizarContadorNotificaciones(true);
        } else {
            alert("Error al borrar todas las notificaciones.");
        }
    };
    xhr.send();
}

function actualizarContadorNotificaciones(reset = false) {
    var countElem = document.querySelector('.notif .count');
    if (reset) {
        countElem.textContent = '0';
    } else {
        var notifItems = document.querySelectorAll('.notif-item').length;
        countElem.textContent = notifItems - 1;
    }
}

function comprobarNotificacionesVacias() {
    var notifItems = document.querySelectorAll('.notif-item').length;
    if (notifItems === 1) {
        var notifDropdown = document.getElementById('notifDropdown');
        notifDropdown.innerHTML = '<div class="notif-item no-notifications"><p class="text-center">No hay notificaciones.</p></div>';
        actualizarContadorNotificaciones(true);
    }
}

// Quitar mensajes de success y error al hacer clic
document.addEventListener('DOMContentLoaded', function () {
    const closeButtons = document.querySelectorAll('.alert .close');

    closeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const alertBox = this.parentElement;

            alertBox.classList.remove('show');
            alertBox.classList.add('fade');

            setTimeout(function () {
                alertBox.style.display = 'none';
            }, 500);
        });
    });
});

function togglePasswordVisibility() {
    var passwordInput = document.getElementById("password");
    var eyeIcon = document.getElementById("eyeIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.classList.remove("bx-show");
        eyeIcon.classList.add("bx-hide");
    } else {
        passwordInput.type = "password";
        eyeIcon.classList.remove("bx-hide");
        eyeIcon.classList.add("bx-show");
    }
}

function togglePasswordVisibilityRepeat() {
    var passwordRepeatInput = document.getElementById("password_repeat");
    var eyeIconRepeat = document.getElementById("eyeIconRepeat");

    if (passwordRepeatInput.type === "password") {
        passwordRepeatInput.type = "text";
        eyeIconRepeat.classList.remove("bx-show");
        eyeIconRepeat.classList.add("bx-hide");
    } else {
        passwordRepeatInput.type = "password";
        eyeIconRepeat.classList.remove("bx-hide");
        eyeIconRepeat.classList.add("bx-show");
    }
}

function togglePasswordVisibilityCurrent() {
    var passwordRepeatInput = document.getElementById("current_password");
    var eyeIconRepeat = document.getElementById("eyeIconCurrent");

    if (passwordRepeatInput.type === "password") {
        passwordRepeatInput.type = "text";
        eyeIconRepeat.classList.remove("bx-show");
        eyeIconRepeat.classList.add("bx-hide");
    } else {
        passwordRepeatInput.type = "password";
        eyeIconRepeat.classList.remove("bx-hide");
        eyeIconRepeat.classList.add("bx-show");
    }
}

function checkPasswordStrength() {
    var password = document.getElementById("password").value;
    var strength = 0;

    if (password.length >= 8) {
        strength += 1;
    }

    if (password.match(/\d+/)) {
        strength += 1;
    }

    if (password.match(/[A-Z]/) && password.match(/[a-z]/)) {
        strength += 1;
    }

    if (password.match(/[!@#$%^&*(),.?":{}|<>]/)) {
        strength += 1;
    }

    var strengthMessage = "";
    var color = "";
    var submitButton = document.getElementById("btnSubmit");

    switch (strength) {
        case 0:
        case 1:
            strengthMessage = "Muy débil";
            color = "red";
            submitButton.disabled = true;
            break;
        case 2:
            strengthMessage = "Débil";
            color = "red";
            submitButton.disabled = true;
            break;
        case 3:
            strengthMessage = "Moderada";
            color = "orange";
            submitButton.disabled = true;
            break;
        case 4:
            strengthMessage = "Fuerte";
            color = "green";
            submitButton.disabled = false; 
            break;
    }

    var strengthElement = document.getElementById("passwordStrength");
    strengthElement.innerHTML = "Fortaleza: " + strengthMessage;
    strengthElement.style.color = color;
}