window.onload = async () => {
    let signed = await checkSigned();
    if (signed) {
        userSigned();
        let items = await getCart();
        let list = document.getElementById('cart__items__list');

        if (items === null) {
            list.textContent = 'Cart is empty!';
        } else {
            generateCartItems(list, items);
        }
    } else {
        alert('You should be signed!');
        fillSignPopup(true);
        active(popup__screen);
        setPopupTitle('Sign In');
    }
    
    checkTheme();

    
};

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

// Returns items from cart
async function getCart() {
    let userId = await getUserId();

    if (userId === undefined) return 1;

    return fetch(`http://localhost:8080/php/cart.php?user_id=${userId}`, { method: 'GET' })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error while savig item to cart');
        }
        return response.json();
    })
    .then(data => { 
        if (data !== null) {
            return JSON.parse(data.items);
        }

        return null;
    })
    .catch(e => console.error(e))
}

function submitOrder() {
    console.log('order')
}

// Depending on id and category return item
async function getItem(item_id, category) {
    return fetch(`http://localhost:8080/php/cart.php?item_id=${item_id}&category=${category}`, { method: 'GET' })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error while getting item from db');
        }
        return response.json();
    })
    .then(data => { return data; })
    .catch(e => console.error(e))
}

async function removeFromCart(category, id) {
    document.getElementById(category + '-' + id).remove();    // Remove from page

    const userId = await getUserId();
    
    if (userId === undefined) return 1;

    fetch('http://localhost:8080/php/cart.php', {
        method: 'DELETE',
        body: JSON.stringify({
            category: category,
            item_id: id,
            user_id: userId
        })
    })
    .then(response => response.text())
    .then(data => console.log(data))
    .catch(e => console.error(e))
}

async function generateCartItems(list, items) {

    async function updateQuantity(category, itemId, newQuantity) {
        let userId = await getUserId();

        if (userId === undefined) return 1;

        fetch('http://localhost:8080/php/cart.php', {
            method: 'PUT',
            body: JSON.stringify({
                category: category,
                item_id: itemId,
                new_quantity: newQuantity,
                user_id: userId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            return response.text();
        })
        .then(data => console.log(data))
        .catch(e => console.error(e))
    }

    async function createItem(item) {
        let itemQuantity = item.quantity;
        let itemCategory = item.category;
        let itemId = item.id;

        item = await getItem(item.id, item.category)

        let div = document.createElement('div');
        div.className = 'cart__item';
        div.id = itemCategory + '-' + itemId;

        let title = document.createElement('div');
        title.className = 'cart__item__title';
        title.textContent = item.title;
        

        let quantityDiv = document.createElement('div');
        quantityDiv.className = 'cart__item__quantity__container';
        let lower = document.createElement('div');
        lower.textContent = '-';
        lower.addEventListener('click', () => {
            --itemQuantity;
            if (itemQuantity === 0) {
                quantity.textContent = '0';
                removeFromCart(itemCategory, itemId);
            } else {
                quantity.textContent = itemQuantity;
                updateQuantity(itemCategory, itemId, itemQuantity);
            }
        });

        let quantity = document.createElement('div');
        quantity.textContent = itemQuantity;
        quantity.id = 'cart__item__quantity';

        let greater = document.createElement('div');
        greater.textContent = '+';
        greater.addEventListener('click', () => {
            itemQuantity++;
            quantity.textContent = itemQuantity;
            updateQuantity(itemCategory, itemId, itemQuantity);
        });

        quantityDiv.appendChild(lower);
        quantityDiv.appendChild(quantity);
        quantityDiv.appendChild(greater);

        div.appendChild(title);
        div.appendChild(quantityDiv);

        return div;
    }

    // Clear list, if exist
    if (list.textContent !== '') {
        list.textContent = '';
    }

    for (let item of items) {
        let itemDiv = await createItem(item);
        list.appendChild(itemDiv);
    }
}