window.onload = async () => {
    let signed = await checkSigned();

    if (signed) {
        userSigned();
        let userId = await getUserId();
        let items = await getCart(userId);
        if (typeof(items) === 'object') items = Object.values(items);   // Transform object to arrray, BUG on php side
        let list = document.getElementById('cart__items__list');

        if (items === null) {
            list.textContent = 'Cart is empty!';
        } else {
            await generateCartItems(list, items)
            setTotalprice(totalPriceContainer);
        }
    } else {
        alert('You should be signed!');
        fillSignPopup(true);
        active(popup__screen);
        setPopupTitle('Sign In');
    }
    
    checkTheme();
};

// Return total price
function calcTotalprice() {
    let total = 0;

    let items = document.getElementById('cart__items__list').querySelectorAll('.cart__item');
    items.forEach(item => {
        let quantity = Number(item.getAttribute('quantity'));
        total += Number(item.getAttribute('price')) * quantity;
    });

    return total;
}

// Sets total price to div
function setTotalprice(totalPriceContainer) {
    totalPriceContainer.textContent = `Total price: ${calcTotalprice()}`;
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

        return response.text();
    })
    .then(data => {
        console.log('Success:', data);
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Returns items from cart
async function getCart(userId) {
    if (userId === undefined) return 1;

    return fetch(`http://localhost:8080/php/cart.php?user_id=${userId}`, { method: 'GET' })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error while getting cart');
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

        item = await getItem(item.id, item.category);

        let div = document.createElement('div');
        div.className = 'cart__item';
        div.id = itemCategory + '-' + itemId;
        div.setAttribute('price', item.price)

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
                setTotalprice(totalPriceContainer);
            } else {
                quantity.textContent = itemQuantity;
                updateQuantity(itemCategory, itemId, itemQuantity);
                div.setAttribute('quantity', itemQuantity);
                setTotalprice(totalPriceContainer);
            }
        });

        let quantity = document.createElement('div');
        quantity.textContent = itemQuantity;
        div.setAttribute('quantity', itemQuantity);
        quantity.id = 'cart__item__quantity';

        let greater = document.createElement('div');
        greater.textContent = '+';
        greater.addEventListener('click', () => {
            itemQuantity++;
            quantity.textContent = itemQuantity;
            updateQuantity(itemCategory, itemId, itemQuantity);
            div.setAttribute('quantity', itemQuantity);
            setTotalprice(totalPriceContainer);
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

async function getMails() {
    return fetch('http://localhost:8080/php/mails.php', {method: 'GET'})
    .then(response => response.json())
    .then(data => { return data; })
    .catch(e => console.error(e))
}

async function fillSubmitPopup() {
    let label = document.createElement('label');
    label.className = 'label_input__container';
    label.textContent = 'Mail';
    label.setAttribute('for', 'mail__select');
    
    let select = document.createElement('select');
    select.className = 'label_input__container';
    select.id = 'mail__select';
    select.setAttribute('name', 'mail__select');
    
    let mails = await getMails();
    
    let emptyOption = document.createElement('option');
    emptyOption.value = '';
    emptyOption.text = 'Select mail: '
    emptyOption.selected = true;
    emptyOption.disabled = true;
    select.appendChild(emptyOption);

    mails.map(mail => {
        const {mail_id, company, department_number, city, street} = mail;

        let option = document.createElement('option');
        option.textContent = company.replace('_', ' ') + ' ' + department_number + ' ' + city;
        select.appendChild(option);
    })

    popUp.insertBefore(label, popupFooter);
    popUp.insertBefore(select, popupFooter);

    let totalPrice = document.createElement('div');
    totalPrice.className = 'total__price__submit'
    setTotalprice(totalPrice);

    popUp.insertBefore(totalPrice, popupFooter)

}

/* Buttons part */
const clearCartBtn = document.getElementById('clear__cart__btn');
const submitOrderBtn = document.getElementById('submit__order__btn');

clearCartBtn.addEventListener('click', async () => {
    const userId = await getUserId();
    clearCart(userId);
});

let mailDiv = createInput('Mail', 'mail');
let mailInput;

submitOrderBtn.addEventListener('click', async () => {
    let userId = await getUserId();
    let cart = await getCart(userId);
    
    if (cart !== null) {
        removeInputsPopup();
        fillSubmitPopup();
        active(popup__screen);
        setPopupTitle('Submit order');
    } else alert('Cart is empty!');
}); 

const totalPriceContainer = document.getElementById('total__price__container');