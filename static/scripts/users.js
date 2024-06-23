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

// Save user email & password in cookie
function saveUser(email, password) {
    document.cookie = `user_email=${encodeURIComponent(email)}; path=/; max-age=604800`;
    document.cookie = `user_password=${encodeURIComponent(password)}; path=/; max-age=604800`;
}

async function getUserId() {
    let email;
    try {
        email = getUserFromCookie()[0];
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

    let email = '';
    let password = '';

    cookies.forEach(cookie => {
        let [name, value] = cookie.split('=');

        name = name.replace(' ', '');   // Remove spaces

        if (name === 'user_email') email = decodeURIComponent(value);
        else if (name === 'user_password') password = decodeURIComponent(value);
    })

    // If userdata empty or undefined, return undefined
    let userdata_undefined = email === 'undefined' && password === 'undefined';
    let userdata_empty = email === '' && password === '';
    if (userdata_undefined || userdata_empty) return undefined;

    return [email, password];
}

/*Sign up part*/
function signUp(user) {
    // Send user's data on server while register
    postRequest(user, 'users.php/signup')
        .then(response => {
            // console.log(response);
            if (response[0]) {
                userSigned();
                let userDB = JSON.parse(response[1]);
                saveUser(userDB.email, userDB.password);
            } else console.log('Something get wrong while sign up');
        });
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
        let userEmail = userCookie[0];
        let userPass = userCookie[1];

        let user = {
            email: userEmail,
            password: userPass
        }

        try {
            const result = await postRequest(user, 'users.php/signin');

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

function signIn(user) {
    postRequest(user, 'users.php/signin')
        .then((response) => {
            if (response[0]) {
                userSigned();
                let userDB = JSON.parse(response[1]);
                clearAllCookies();
                saveUser(userDB.email, userDB.password);
            } else {
                alert('Something get wrong! Look console logs');
            }
        })
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