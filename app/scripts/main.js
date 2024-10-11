window.onload = async () => {
    let signed = await checkSigned();
    if (signed) userSigned();

    checkTheme();
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

// const headerTitle = document.getElementById('header__title');
// headerTitle.addEventListener('click', () => {
//     window.location.href = '../../index.php';
// });

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