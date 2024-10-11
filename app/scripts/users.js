const signinBtn = document.getElementById('sign_inBtn');
const signupBtn = document.getElementById('sign_upBtn');

/*Pop up part*/
// Insert inputs for sign in/up in modal window
function fillSignPopup(signIn = false) {

    // Add nickname div with input while Sign up
    if (!signIn) {
        popupMain.appendChild(nickDiv);
        nicknameInput = document.getElementById('nickname');
        popupMain.appendChild(phoneDiv);
        phoneInput = document.getElementById('phone');
    }

    // Insert input's div in pop-up
    popupMain.appendChild(emailDiv);
    popupMain.appendChild(passDiv);

    // Assign inputs
    emailInput = document.getElementById('email');
    passInput = document.getElementById('password');
    passInput.setAttribute('type', 'password');
}

/*inputs part*/
// Init input containers
// const emailDiv = createInput('Email', 'email');
// const passDiv = createInput('Password', 'password');
// const nickDiv = createInput('Nickname', 'nickname');
// const phoneDiv = createInput('Phone', 'phone');

// let emailInput, passInput, nicknameInput, phoneInput;   // Init inputs

// Save JWT in cookie
function saveJWT(jwt) {
    document.cookie = `jwt=${encodeURIComponent(jwt)}; path=/; max-age=604800`;
}

async function getUserId() {
    let jwt = getJwtFromCookie();
    let requestData = { route: 'users.php/get_user_id', method: 'GET', jwt: jwt};
    let response = await request(requestData);
    return response.user_id;
}

function getJwtFromCookie() {
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
    let requestData = { route: 'users.php/signup', method: 'POST', body: user };
    let data = await request(requestData);

    if (data.status) {
        saveJWT(data.jwt);
        userSigned();
    } else console.error('Error while trying sign up: ', data.message);
}

// Activate Pop-up screen with pop-up container
// signupBtn.addEventListener('click', () => {
//     window.location.href = 'pages/sign_form.php';
//     removeInputsPopup();
//     fillSignPopup();
//     setPopupTitle('Sign Up');
//     nickDiv.style.display = 'flex'; // Show nickname input
//     active(popup__screen);
// });


/*Sign in part*/
// Change UI in case that user signed
function userSigned() {
    signoutBtn.style.display = "inline-block";
    hideSigninBtns();
    active(popup__screen, false);
    removeInputsPopup();
    signed = true;
}

// If JWT exist send it to server to sign in
async function checkSigned() {
    let jwt = getJwtFromCookie();

    if (jwt) {
        let requestData = { route: 'users.php/sign_in_jwt', method: 'GET', jwt: jwt };
        const result = await request(requestData);
        
        if (result.status) {
            userSigned();
            return true; 
        } else {
            showSigninBtns();
            console.log("Auto login failed: ", result.message);
        }
    } else {
        console.log("JWT not finded");
        showSigninBtns();
    }
    return false;
}

async function signIn(user) {
    let requestData = { route: 'users.php/signin', method: 'POST', body: user}
    let data = await request(requestData);

    console.log(data);
    console.log(data.message);

    if (data) {
        saveJWT(data.jwt);
        userSigned();

        if (window.location.href.indexOf('static/pages/cart.php') !== -1) location.reload();
    } else console.error('Receive null in request!');
}

// signinBtn.addEventListener('click', () => {
//     removeInputsPopup();
//     fillSignPopup(true);
//     setPopupTitle('Sign In');
//     nickDiv.style.display = 'none'; // Hide nickname input
//     active(popup__screen);
// });


/*Sign out part*/
const signoutBtn = document.getElementById('sign_outBtn');
signoutBtn.style.display = "none";

signoutBtn.addEventListener('click', () => {
    clearAllCookies();
    location.reload();  // Reload page
});