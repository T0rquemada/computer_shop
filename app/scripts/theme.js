// Check current color theme
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