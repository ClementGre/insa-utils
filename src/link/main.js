document.querySelectorAll('form input[name="title"]').forEach((a) => {
    // Select the textarea following the input when pressing enter
    a.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            a.nextElementSibling.focus();
        }
    });
});


const section = document.querySelector('#results-section')
const search_stats = document.querySelector('#search-stats')
const no_result_html = '<p class="no-results">Aucun résultat</p>'

function updateLinks(query){
    fetch(getRootPath() + 'link/jsapi/search', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({'query': query, 'user_id': getUserId(), "csrf_js": getCsrfToken()})
    })
        .then((response) => {
            return response.json();
        })
        .then((res) => {
            if (res['status'] === 'done') {
                let hits_count = res['hits_count'];
                let approx = res['hits_count_estimated'] ? '~' : '';
                search_stats.innerHTML = approx + hits_count + ' résultats en ' + res['processing_time_ms'] + ' ms';
                section.innerHTML = prettyPrintJSONtoHTML(res['hits'])
            } else if (res['status'] === 'invalid_csrf') {
                location.reload();
            }

        })
}

document.querySelector('#search-input').addEventListener('input', (e) => {
    let query = e.target.value;
    if (query.length > 0) {
        updateLinks(query)
    } else {
        section.innerHTML = no_result_html;
    }
})


