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

let last_query_time = 0;

function updateLinks(query){
    last_query_time = new Date() / 1;
    let this_query_time = new Date() / 1;
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
            if (this_query_time < last_query_time) {
                return;
            }
            if (res['status'] === 'done') {
                let hits_count = res['hits_count'];
                let approx = res['hits_count_estimated'] ? '~' : '';
                search_stats.innerHTML = approx + hits_count + ' résultats en ' + res['processing_time_ms'] + ' ms';
                section.innerHTML =  res['hits'].map((data) => getLinkHtml(data)).join('');
            } else if (res['status'] === 'invalid_csrf') {
                location.reload();
            }

        })
}
function getLinkHtml($data){

    let html = `
        <h2>` + $data['title'] + `</h2>
        <hr>
        <p class="description">` + $data['description'] + `</p>
        <div class="details">
            <a target="_blank" href="` + $data['link'] + `">` + $data['link'] + `</a>
            <div class="mutable-content">
                <button class="more-button">Détails</button>
                <p class="author"></p>
            </div>
            <button class="rating like"><img src="` + getRootPath() + `svg/like.svg" alt="Supprimer">` + $data['likes'] + `</button>
            <button class="rating dislike"><img src="` + getRootPath() + `svg/dislike.svg" alt="Supprimer">` + $data['dislikes'] + `</button>
        </div>
    `

    return '<div class="link">' + html + '</div>';
}

let last_input_time = 0;
document.querySelector('#search-input').addEventListener('input', (e) => {
    last_input_time = new Date() / 1;
    setTimeout(() => {
        if (new Date() / 1 - last_input_time >= 300) {
            let query = e.target.value;
            if (query.length > 0) {
                updateLinks(query)
            } else {
                last_query_time = new Date() / 1;
                section.innerHTML = no_result_html;
            }
        }
    }, 300);
})


