let sortOrder = undefined;
let route = undefined;

let items = []; // Contain items specific category
let activeFilters = {}; // Contain all filters for items specific category

function get_items_request(route) {
    return fetch(`http://localhost:8080/php/catalog.php${route}`, {method: 'GET'})
        .then(response => response.json())
}

async function get_items(route) {
    return await get_items_request(route)
}

const itemsList = document.getElementById('items__list');

// Return item in div
function createItem(item, categoryTitle) {

    let itemId;

    if (categoryTitle === 'motherboards') {
        let motherboard_id = categoryTitle.slice(0, categoryTitle.length-1)
        itemId = item[motherboard_id + '_id'];
    } else {
        itemId = item[categoryTitle + '_id'];
    }

    let itemCategory = categoryTitle;

    let {title, brand, price} = item;
    const item_container = document.createElement('div');
    item_container.id = itemId;
    item_container.setAttribute('category', itemCategory);
    item_container.className = 'item__container';

    let upperPart = document.createElement('div');

    let itemTitle = document.createElement('div');
    itemTitle.className = 'item__title';
    if (title === ' ') itemTitle.textContent = brand;
    else itemTitle.textContent = title;

    let itemPrice = document.createElement('div');
    itemPrice.className = 'item__price';
    itemPrice.textContent = price + '\u20B4';       // Ukrainian grivnya symbol

    upperPart.appendChild(itemTitle);

    let lowerPart = document.createElement('div');
    lowerPart.style.textAlign = 'center';

    let buyBtn = document.createElement('button');
    buyBtn.className = 'item__buybtn';
    buyBtn.textContent = 'Add to cart'

    buyBtn.addEventListener('click', () => {
        console.log(item_container.id);
        console.log(item_container.getAttribute('category'));
    });

    lowerPart.appendChild(itemPrice);
    lowerPart.appendChild(buyBtn);

    item_container.appendChild(upperPart);
    item_container.appendChild(lowerPart);

    return item_container;
}

function sortItems(items) {
    if (sortOrder === 'asc') {
        items.sort(function(a, b) {
            return a.price - b.price;
        });
    } else if (sortOrder === 'desc') {
        items.sort(function(a, b) {
            return b.price - a.price;
        });
    }
    return items;
}

// Return object, where keys - filter words, values - filtering values
function aggregateValues(arrayOfObjects) {
    const result = {};

    arrayOfObjects.forEach(obj => {
        Object.keys(obj).forEach(key => {
            if (key.toLowerCase().includes("id") || key.toLowerCase() === "title") {
                return; // Skip '*_id*' and 'title' key
            }

            // If key doesn't exist in object, initialize it with an empty set
            if (!result[key]) {
                result[key] = new Set();
            }

            // Add the value of the current key
            result[key].add(obj[key]);
        });
    });

    return result;
}

// Return div, with one filter section: title, and values for filtering
function createFilter(key, values) {
    const filter = document.createElement("div");
    filter.className = 'filter__container'

    let title = key.replace('_', ' ');
    title = title.charAt(0).toUpperCase() + title.slice(1);

    let filterKeyDIv = document.createElement('div');
    filterKeyDIv.className = 'filter__key';
    filterKeyDIv.textContent = title;

    filter.appendChild(filterKeyDIv);

    values.forEach((value) => {

        // Replace '0' and '1' (true/false in php) to human-readable
        if (key === 'integrated_graphics') {
            if (value === 0) value = 'Not';
            else if (value === 1) value = 'Yes';
        }

        let valuesContainer = document.createElement('div');

        let valueInput = document.createElement('input');
        valueInput.id = key + '_' + value;
        valueInput.name = key;
        valueInput.type = 'radio';

        valueInput.addEventListener('change', () => {
            activeFilters[key] = value;
            generateItems(items, route.slice(1));
        });

        let valueLabel = document.createElement('label');
        valueLabel.setAttribute('for', valueInput.id);
        valueLabel.textContent = ' ' + value;

        valuesContainer.appendChild(valueInput);
        valuesContainer.appendChild(valueLabel);

        filter.append(valuesContainer);
    });

    return filter;
}

// Return filters div
function createFiltersDiv(keys, values) {
    let oldFilters = document.getElementById('sidebar__filters');

    // Clear previous filters, if they exist
    if (oldFilters !== null) sidebarContainer.removeChild(oldFilters);

    const filtersContainer = document.createElement('div');
    filtersContainer.id = 'sidebar__filters';
    filtersContainer.className = 'sidebar__part';

    const filtersTitle = document.createElement('div');
    filtersTitle.className = 'sidebar__title';
    filtersTitle.textContent = 'Filters';

    filtersContainer.appendChild(filtersTitle);

    let keysLength = keys.length;
    for (let i = 0; i < keysLength; i++) {
        filtersContainer.appendChild(createFilter(keys[i], values[i]));
    }

    return filtersContainer;
}

// Add filters div in sidebar
function addFilterToSidebar(items) {
    activeFilters = {}; // Clear filters

    const filters = aggregateValues(items);

    let filterKeys = Object.keys(filters);
    filterKeys.forEach(key => activeFilters[key] = '');

    let filterValues = Object.values(filters);
    const sidebarFilters = createFiltersDiv(filterKeys, filterValues);
    
    sidebarContainer.appendChild(sidebarFilters);   // New
    // Old: if (sidebarFilters !== null) sidebarContainer.appendChild(sidebarFilters);
}

// Return true if any filters enabled, otherwise - false
function checkFilters() {
    return Object.values(activeFilters).some(filter => filter !== '');
}

// Will return key-value of active filters
function getActiveFilters(activeFilters) {
    return Object.entries(activeFilters).filter(([key, value]) => value !== '');
}

// Filter given items, and return array this items
function filterItems(items) {
    const filters = getActiveFilters(activeFilters);

    return items.filter(item => {
        return filters.every(([key, value]) => item[key] === value);
    })
}

function generateItems(items, categoryTitle) {
    itemsList.textContent = '';

    try {
        if (checkFilters()) {
            items = filterItems(items);
        }

        items = sortItems(items);
    
        items = items.map(item => createItem(item, categoryTitle));    // Create divs for items
        items.forEach(item => itemsList.appendChild(item)); // Fill item list
    } catch (e) {console.error('Error while generating items: ', e)}
}

// Categories divs
const cpuDiv = document.getElementById("cpu__category__div");
const gpuDiv = document.getElementById("gpu__category__div");
const motherboardsDiv = document.getElementById("motherboards__category__div");
const ramDiv = document.getElementById("ram__category__div");
const sidebarContainer = document.getElementById('sidebar__container');

const categories = [cpuDiv, gpuDiv, motherboardsDiv, ramDiv];

// Add event listeners on every category in sidebar;
categories.forEach(category => {
    category.addEventListener("click", async () => {
        let categoryTitle = category.id;
        categoryTitle = categoryTitle.slice(0, categoryTitle.indexOf('__')); // Extract category title from id
        route = '/' + categoryTitle;    // Same as categoryTitle, but with '/' on start
        
        items = await get_items(route);
        addFilterToSidebar(items); 
        generateItems(items, categoryTitle);
    })
});

let descRadio = document.getElementById("desc__radio");
let ascRadio = document.getElementById("asc__radio");

// Event listeners for the radio buttons
ascRadio.addEventListener("change", function() {
    if (ascRadio.checked) {
        sortOrder = 'asc';
        if (route !== undefined) generateItems(items);  // Re-generate items
    }
});

descRadio.addEventListener("change", function() {
    if (descRadio.checked) {
        sortOrder = 'desc';
        if (route !== undefined) generateItems(items);
    }
});