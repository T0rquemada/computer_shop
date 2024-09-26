const signinBtn = document.getElementById('sign_inBtn');
const signupBtn = document.getElementById('sign_upBtn');


/*Pop up part*/
// Insert inputs for sign in/up in modal window
function fillSignPopup(signIn = false) {

    // Add nickname div with input while Sign up
    if (!signIn) {
        popUp.insertBefore(nickDiv, popupFooter);
        nicknameInput = document.getElementById('nickname');
        popUp.insertBefore(phoneDiv, popupFooter);
        phoneInput = document.getElementById('phone');
    }

    // Insert input's div in pop-up
    popUp.insertBefore(emailDiv, popupFooter);
    popUp.insertBefore(passDiv, popupFooter);

    // Assign inputs
    emailInput = document.getElementById('email');
    passInput = document.getElementById('password');
    passInput.setAttribute('type', 'password');
}


/*inputs part*/
// Init input containers
const emailDiv = createInput('Email', 'email');
const passDiv = createInput('Password', 'password');
const nickDiv = createInput('Nickname', 'nickname');
const phoneDiv = createInput('Phone', 'phone');

let emailInput, passInput, nicknameInput, phoneInput;   // Init inputs

// Save JWT in cookie
function saveJWT(jwt) {
    document.cookie = `jwt=${encodeURIComponent(jwt)}; HttpOnly; SameSite=Strict; path=/; max-age=604800`;
}

async function getUserId() {
    let email;
    try {
        email = getUserFromCookie();
    } catch (e) {
        console.error(e);
        return undefined;
    }
    return fetch(`http://localhost:8080/php/users.php/userid?email=${email}`, {
        method: 'GET',
        headers: {'Content-Type': 'application/json'}
    })
    .then(response => response.json())
    .then(data => {return data})
    .catch(e => console.error(e))
}

function getUserFromCookie() {
    const cookies = document.cookie.split(';');

    let jwt = cookies
        .map(cookie => cookie.trim())
        .find(cookie => cookie.startsWith('jwt='));

    if (jwt) {
        jwt = jwt.split('=')[1];
        jwt = decodeURIComponent(jwt);
    }

    if (!jwt || jwt === 'undefined') return undefined;

    return jwt;
}

/*Sign up part*/
async function signUp(user) {
    let data = await request('POST', user, 'users.php/signup');
    if (data) {
        saveJWT(data.jwt);
        userSigned();
    } else console.error('Receive null in request: ');
}

// Activate Pop-up screen with pop-up container
signupBtn.addEventListener('click', () => {
    removeInputsPopup();
    fillSignPopup();
    setPopupTitle('Sign Up');
    nickDiv.style.display = 'flex'; // Show nickname input
    active(popup__screen);
});


/*Sign in part*/
// Change UI in case that user signed
function userSigned() {
    signoutBtn.style.display = "inline-block";
    hideSigninBtns();
    active(popup__screen, false);
    removeInputsPopup();
    signed = true;
}

// Return true | false | undefined
async function checkSigned() {
    let userCookie = getUserFromCookie();

    if (userCookie !== undefined) {
        let user = {
            jwt: userCookie
        }

        try {
            const result = await request('POST', user, 'users.php/signin');

            if (result) {
                userSigned();
                return true; 
            } else {
                showSigninBtns();
                console.log("User credentials incorrect, received false from server.");
                return false;
            }
        } catch (e) {
            console.error(e);
            showSigninBtns();
        }
    } else {
        console.log("Cookies empty");
        showSigninBtns();
        return undefined;
    }
}

async function signIn(user) {
    let data = await request('POST', user, 'users.php/signin');

    if (data) {
        saveJWT(data.jwt);
        userSigned();

        if (window.location.href.indexOf('static/pages/cart.php') !== -1) location.reload();
    } else console.error('Receive null in request!');
}

signinBtn.addEventListener('click', () => {
    removeInputsPopup();
    fillSignPopup(true);
    setPopupTitle('Sign In');
    nickDiv.style.display = 'none'; // Hide nickname input
    active(popup__screen);
});


/*Sign out part*/
const signoutBtn = document.getElementById('sign_outBtn');
signoutBtn.style.display = "none";

// Clear data stored on client
signoutBtn.addEventListener('click', () => {
    clearAllCookies();
    location.reload();  // Reload page
});