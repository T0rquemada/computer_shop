window.onload = async () => {
    let signed = await checkSigned();
    if (signed) userSigned();

    checkTheme();
}

function setPopupTitle(title) {
    popupTitle.textContent = title;
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

async function request(method, php_file, object=undefined) {
    if (!method) throw new Error('Method for request not provided!');

    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (method !== 'GET') options.body = JSON.stringify(object);

        let response = await fetch(`http://localhost:8080/php/${php_file}`, options);
        let data = await response.json();
        if (!response.ok) throw new Error(`Error ${response.status}, ${data.message}`);

        return await data;
    } catch (err) {
        alert(`Request failed: ${err.message}`);
        return null;
    }
}

const headerTitle = document.getElementById('header__title');
headerTitle.addEventListener('click', () => {
    window.location.href = '../../index.php';
});

/*Pop up part*/
const popup__screen = document.getElementById('pop_up__screen');
const popUp = document.getElementById('pop_up__container');
const popupClose = document.getElementById('pop_up__close');
const popupCancel = document.getElementById('pop_up__cancel');
const popupSubmit = document.getElementById('pop_up__submit');
const popupTitle = document.getElementById('pop_up__title');
const popupFooter = document.getElementById('pop_up__footer');

function closePopup() {
    active(popup__screen, false);
    removeInputsPopup();
}

// Hide pop up on Close
popupClose.addEventListener('click', () => {
    closePopup();
});

// Hide pop-up when user press 'Escape'
window.addEventListener('keydown', (key) => {
    if (key.key === 'Escape') {
        closePopup();
    } else if (key.key === 'Enter') {
        submitPopup();
    }
});

popupCancel.addEventListener('click', () => {
    closePopup();
});

// Resposne for submitting pop-up
function submitPopup() {
    if (checkPopupInputs() === false && popupTitle.textContent !== 'Submit order') {
        alert('All fields must be filled!');
    } else {
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
}

popupSubmit.addEventListener('click', () => {
    submitPopup();
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
            user.nickname = nicknameInput.value
            user.phone = phoneInput.value;
        }

        func(user);
    }
}

// If even one input Pop-up empty, return false
function checkPopupInputs() {

    if (popupTitle.textContent === 'Submit order') return false;

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

function checkTheme() {
    let currentTheme = localStorage.getItem('theme');

    if (currentTheme === 'white')  changeTheme('white');
    else if (currentTheme === 'black') changeTheme('black');
}

function switchWhiteTheme() {
    root.style.setProperty('--white', 'black');
    root.style.setProperty('--black', 'white');
}

function switchBlackTheme() {
    root.style.setProperty('--white', 'white');
    root.style.setProperty('--black', 'black');
}

function changeTheme(localTheme=undefined) {
    if (localTheme !== undefined) {
        if (localTheme === 'black') {
            switchBlackTheme();
        } else if (localTheme === 'white') {
            switchWhiteTheme();
        }

        return;
    }
    
    let currentColor = getComputedStyle(root).getPropertyValue('--black').trim();
    
    if (currentColor === 'white') {
        switchBlackTheme();
        localStorage.setItem('theme', 'black');
    } else {
        switchWhiteTheme();
        localStorage.setItem('theme', 'white');
    }
}

const themeBtn = document.getElementById('theme_switcher');
const root = document.documentElement;

themeBtn.addEventListener('click', () => {
    changeTheme();
});

/*Cart part*/
let cartBtn = document.getElementById('items_cart');

cartBtn.addEventListener('click', async () => {
    let signed = await checkSigned();
    if (signed) {
        if (window.location.href.indexOf('static/pages/cart.php') === -1) {
            window.location.href = 'static/pages/cart.php';
        }
    } else {
        alert('You should be signed!');
    }
    
});