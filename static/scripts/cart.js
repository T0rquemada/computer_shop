let cartBtn = document.getElementById('items_cart');

async function saveToCart(item) {
    let userId = await getUserId();

    fetch('http://localhost:8080/php/cart.php/updatecart', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({user_id: userId, item: item})
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error while savig item to cart');
        }
        return response.json();
    })
    .then(data => console.log(data))
    .catch(e => console.error(e))
}

function clearCart(user_id) {
    fetch('http://localhost:8080/php/cart.php', {
        method: 'DELETE',
        headers: {
            'Content-type': 'application/json'
        },
        body: JSON.stringify({user_id: user_id})
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok ' + response.statusText)
        }

        return response.json();
    })
    .then(data => {
        console.log('Success:', data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function getCurrentCart() {
    let items = localStorage.getItem('cart');
    if (items) {
        return JSON.parse(items);
    }

    return undefined;
}

function addToCart(id, category) {
    let item = {
        id: id,
        category: category
    };
}

function submitOrder() {
    console.log('order')
}

function fillCartPopup() {

}

cartBtn.addEventListener('click', () => {
    if (signed) {
        active(popup__screen);
        popupTitle.textContent = 'Cart';
    } else {
        alert('You should be signed!');
    }
    
});