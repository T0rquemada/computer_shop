window.onload = () => {
    checkSigned(); // Check user credentials in cookies
}

// Add/remove 'active' class for HTML element
function active(element, status= true) {
    if (status === true) {
        element.classList.add('active');
    } else if (status === false) {
        element.classList.remove('active');
    } else {
        throw new Error(`Unexpected argument for function 'active': expected true or false, received '${typeof(status)}' with value ${status}`);
    }
}

function clearAllCookies() {
    const cookies = document.cookie.split(';');

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf('=');
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;`;
    }
}

function hideSigninBtns() {
    signinBtn.style.display = "none";
    signupBtn.style.display = "none";
}

function showSigninBtns() {
    signupBtn.style.display = "inline-block";
    signinBtn.style.display = "inline-block";
}

// Make POST-request on server with endpoint, sending object
function postRequest(object, php_file) {
    return fetch(`http://localhost:8080/php/${php_file}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(object)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Fail while request on server');
            }
            return response.text();
        })
        .then(data => {
            if (data.indexOf('!') !== -1) {
                console.log('Response from server: ', data.slice(0, data.indexOf('!')+1)); // Print response without userdata
            } else console.log('Response from server: ', data);

            let response = data;

            // In sign in/up case, return true and array with user data
            if (response.indexOf('"email":') !== -1 && response.includes('successfully')) { //Check that we retrieve user in json
                response = response.slice(response.indexOf('!')+1);
                let user = response;

                return [true, user];
            }

            if (response.includes('User with this email does not exist!') || response.includes('Wrong password!')) {
                return false;
            }

            if (response.includes('User already exist!')) {
                return 'User already exist!';
            }

            return false;
        })
        .catch(error => {
            console.error('Error:', error);
            return false;
        });
}


/*Pop up part*/
const popup__screen = document.getElementById('pop_up__screen');
const popUp = document.getElementById('pop_up__container');
const popupClose = document.getElementById('pop_up__close');
const popupCancel = document.getElementById('pop_up__cancel');
const popupSubmit = document.getElementById('pop_up__submit');
const popupTitle = document.getElementById('pop_up__title');
const popupFooter = document.getElementById('pop_up__footer');

// Hide pop up on Close
popupClose.addEventListener('click', () => {
    active(popup__screen, false);
    removeInputsPopup();
});

// Hide pop-up when user press 'Escape'
window.addEventListener('keydown', (key) => {
    if (key.key === 'Escape') {
        active(popup__screen, false);
        removeInputsPopup();
    }
});

popupCancel.addEventListener('click', () => {
    active(popup__screen, false);
    removeInputsPopup();
});

popupSubmit.addEventListener('click', () => {
    if (checkPopupInputs() === false) {
        alert('All fields must be filled!');
    } else {
        switch (popupTitle.textContent) {
            case 'Sign Up':
                submitUser(signUp);
                break;
            case 'Sign In':
                submitUser(signIn, true);
                break;
            default:
                throw new Error('Error while submitting...');
        }
    }
});


/*Popup input part*/
// Submit user in pop up
function submitUser(func, signin=false) {
    if (!emailInput.value.includes('@')) {
        alert('Email must contain "@"!');
    } else {
        // Form user to send on server
        let user = {
            email: emailInput.value,
            password: passInput.value
        };

        if (!signin) {
            user = {nickname: nicknameInput.value, ...user};
            user = {phone: phoneInput.value, ...user};
        }

        func(user);
    }
}

// If even one input Pop-up empty, return false
function checkPopupInputs() {
    const inputsDiv = document.querySelectorAll('.label_input__container');
    let inputs = [];
    inputsDiv.forEach(div => inputs.push(div.querySelector('input')));

    let correct = true;

    inputs.forEach((x) => {
        if (x.tagName !== 'INPUT') {    // Check that arr correct
            throw new Error(`Unexpected element: expect inputs, receive: ${x}`);
        }

        if (x.value === '' || x.value === ' ') {
            correct = false;
        }
    });

    return correct;
}

// Return div, with label + input pair
function createInput(inputTitle, id) {
    let container = document.createElement('div');
    container.className = 'label_input__container';
    container.id = id + '_container';

    let label = document.createElement('label');
    label.setAttribute('for', id);
    label.textContent = inputTitle;
    container.append(label);

    let input = document.createElement('input');
    input.id = id;
    input.setAttribute('placeholder', `Enter ${inputTitle.toLowerCase()}...`);
    input.setAttribute('autocomplete', 'off');
    container.append(input);

    return container;
}

// Remove all inputs from popUp and clear their values
function removeInputsPopup() {
    const inputContainers = document.querySelectorAll('.label_input__container');

    // Clear input values
    inputContainers.forEach((container) => {
        const inputs = container.querySelectorAll('input');
        inputs.forEach(input => input.value = '');
    });

    inputContainers.forEach(element => element.remove());
}

function changeTheme() {
    let currentColor = getComputedStyle(root).getPropertyValue('--white').trim();

    if (currentColor === 'white') {
        root.style.setProperty('--white', 'black');
        root.style.setProperty('--black', 'white');
    } else {
        root.style.setProperty('--white', 'white');
        root.style.setProperty('--black', 'black');
    }
}

const themeBtn = document.getElementById('theme_switcher');
const root = document.documentElement;

themeBtn.addEventListener('click', () => {
    changeTheme();
});