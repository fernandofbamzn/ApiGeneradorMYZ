function displayEndpoints(categories) {
    let output = '';
    for (const category of categories) {
        output += `
            <div class="category">
                <h2 class="category-header">${category.category}</h2>
                <div class="category-content">
        `;
        const apiUrl = 'https://fernandofbamzn.duckdns.org/ApiGeneradorMYZ/api.php';
        for (const endpoint of category.endpoints) {
            output += `
                <div class="endpoint">
                    <h3>${endpoint.name}</h3>
                    ${endpoint.desc ? `<p class="endpoint-desc">${endpoint.desc}</p>` : ''}
                    <p class="method">${endpoint.method} - ${endpoint.url}</p>                    
                    <pre class="body">${apiUrl}${endpoint.url.replace('{{url}}', '')}</pre>
                    ${endpoint.body ? `<pre class="body">${JSON.stringify(JSON.parse(endpoint.body), null, 2)}</pre>` : ''}
                </div>
                <hr>
            `;
        }
        output += `
                </div>
            </div>
        `;
    }

    $('#endpoints').html(output);
}

$(document).ready(function() {
    $.getJSON('llamadas_api.json', function(data) {
        let categories = parsePostmanJson(data);
        displayEndpoints(categories);

        // Ocultar todas las categorías al principio
        $('.category-content').hide();

        // Mostrar u ocultar la categoría al hacer clic en el encabezado
        $('.category-header').click(function() {
            $(this).next('.category-content').toggle();
        });
    });
});

function parsePostmanJson(data) {
    let categories = [];

    for (let categoryData of data) {
        let category = { category: categoryData.category, endpoints: [] };
        for (let endpointData of categoryData.endpoints) {
            const name = endpointData.name;
            const method = endpointData.method;
            const url = endpointData.url;
            const desc =  endpointData.desc ? endpointData.desc : null; // Comprobar si el endpoint tiene una desc
            const body = endpointData.body ? endpointData.body : null; // Comprobar si el endpoint tiene un cuerpo

            category.endpoints.push({ name, method, url, desc, body }); // Incluir el cuerpo del endpoint en la estructura de datos
        }
        categories.push(category);
    }

    return categories;
}
