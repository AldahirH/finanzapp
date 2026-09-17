const loginButton = document.getElementById("show-login");
const registerButton = document.getElementById("show-register");

const loginPanel = document.getElementById("login-panel");
const registerPanel = document.getElementById("register-panel");

loginButton.addEventListener("click", function(){
    changePanel(true);
});

registerButton.addEventListener("click", function(){
    changePanel(false);
});

const tabButtons = [loginButton, registerButton];

tabButtons.forEach(function (button) {
    button.addEventListener("keydown", function (event) {
        let nextButton;

        if (event.key === "ArrowRight" || event.key === "ArrowLeft") {
            // Como solo hay dos pestañas, cualquiera de las flechas
            // cambia a la otra.
            nextButton = button === loginButton
                ? registerButton
                : loginButton;
        } else if (event.key === "Home") {
            nextButton = loginButton;
        } else if (event.key === "End") {
            nextButton = registerButton;
        } else {
            return;
        }

        event.preventDefault();
        changePanel(nextButton === loginButton);
        nextButton.focus();
    });
});

function changePanel(showLogin){
    // Mostrar un panel y ocultar el otro.
    loginPanel.hidden = !showLogin;
    registerPanel.hidden = showLogin;

    // Marcar visualmente la pestaña elegida.
    loginButton.classList.toggle("isActive", showLogin);
    registerButton.classList.toggle("isActive", !showLogin);

    // Comunicar qué pestaña está seleccionada.
    loginButton.setAttribute("aria-selected", String(showLogin));
    registerButton.setAttribute("aria-selected", String(!showLogin)); 

    // La pestaña activa será la entrada al grupo con la tecla Tab.
    loginButton.tabIndex = showLogin ? 0 : -1;
    registerButton.tabIndex = showLogin ? -1 : 0;
}