const form = document.getElementById('form');
const popupClose = document.getElementById('pop_up__close');
const popupCancel = document.getElementById('pop_up__cancel');
const popupSubmit = document.getElementById('pop_up__submit');

// Hide pop up on Close
popupClose.addEventListener('click', () => {
    window.location.href = '../../index.php';
});

popupCancel.addEventListener('click', () => {
    window.location.href = '../../index.php';
});

form.addEventListener('submit', (event) => {
    event.preventDefault();
    submitPopup();
});

// Hide pop-up when user press 'Escape'
window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        window.location.href = '/';
    }
});

// Resposne for submitting pop-up
function submitPopup() {
    switch (popupTitle.textContent) {
        case 'Sign Up':
            submitUser(signUp);
            break;
        case 'Sign In':
            submitUser(signIn, true);
            break;
        case 'Submit order':
            submitOrder();
            break;
        default:
            throw new Error('Error while submitting...');
    }
}

// Submit user in pop up
function submitUser(func, signin=false) {
    // Form user to send on server
    let user = {
        email: emailInput.value,
        password: passInput.value
    };

    if (!signin) {
        user.nickname = nicknameInput.value
        user.phone = phoneInput.value;
    }

    func(user);
}

// Remove all inputs from popUp and clear their values
// function removeInputsPopup() {
//     const inputContainers = document.querySelectorAll('.label_input__container');

//     // Clear input values
//     inputContainers.forEach((container) => {
//         const inputs = container.querySelectorAll('input');
//         inputs.forEach(input => input.value = '');
//     });

//     inputContainers.forEach(element => element.remove());
// }