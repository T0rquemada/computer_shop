async function request(requestData) {
    let { route, method, body, jwt } = requestData;
    if (!route) throw new Error('Route for request not provided!');
    if (!method) throw new Error('Method for reqeust not provided!');
    if (!body && method !== 'GET') throw new Error('Body for request not provided!');

    try {
        let headers = { 'Content-Type' : 'application/json' };
        if (jwt) headers['Authorization'] = `Bearer ${jwt}`;

        let options = {
            method: method,
            headers: headers
        }

        if (body) options.body = JSON.stringify(body);

        const response = await fetch(`http://localhost:8080/php/${route}`, options);

        if (!response.ok) { 
            const error = await response.json();
            console.error(`Error while ${route}: ${error.message || 'Unknown error'}`); 
            return null; 
        }

        const data = await response.json();

        return data;
    } catch (err) {
        console.error(err);
    }
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
    input.setAttribute('required', '');
    container.append(input);

    return container;
}