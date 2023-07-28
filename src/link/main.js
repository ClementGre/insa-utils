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
const search_input = document.querySelector('#search-input')
const search_stats = document.querySelector('#search-stats')
const no_result_html = '<p class="no-results">Aucun résultat</p>';
const loader = '<div class="loader"></div>';

let last_query_time = 0;

let opened_details = null
let editing_link_el = null;

let last_query = new URLSearchParams(window.location.search).get('q');
updateLinks(last_query)
search_input.value = last_query;


let last_input_time = 0;
search_input.addEventListener('input', (e) => {
    last_input_time = new Date() / 1;
    setTimeout(() => {
        if (new Date() / 1 - last_input_time >= 300) {
            updateLinks(e.target.value)
        }
    }, 300);
})

/**
 * @param query
 * @param offset If different from 0, the results will be appended to the current ones
 */
function updateLinks(query, offset = 0) {
    last_query_time = new Date() / 1;
    let this_query_time = new Date() / 1;

    if (offset === 0) {
        opened_details = null;
        editing_link_el = null;
        section.innerHTML = loader;
    } else {
        section.querySelector('#more-button')?.remove()
        section.innerHTML += loader;
    }
    search_stats.innerHTML = 'Recherche en cours...';

    fetch(getRootPath() + 'link/jsapi/search', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({'query': query, 'user_id': getUserId(), 'offset': offset * 20, "csrf_js": getCsrfToken()})
    })
        .then((response) => {
            return response.json();
        })
        .then((res) => {
            if (this_query_time < last_query_time) {
                return;
            }
            if (res['status'] === 'done') {
                opened_details = null;
                editing_link_el = null;
                last_query = query;

                section.querySelector('.loader')?.remove();

                let hits_count = res['hits_count'];
                if (hits_count === 0) {
                    search_stats.innerHTML = '0 résultats en ' + res['processing_time_ms'] + ' ms';
                    section.innerHTML = no_result_html;
                    return;
                }
                let approx = res['last_offset'] ? '' : '~';

                search_stats.innerHTML = approx + hits_count + ' résultats en ' + res['processing_time_ms'] + ' ms';
                section.innerHTML += res['hits'].map((data) => getLinkHtml(data)).join('');

                section.querySelectorAll('li.link').forEach((div) => {
                    div.querySelector('button.more-button').addEventListener('click', (e) => {
                        hide_edit_link_form();
                        if (opened_details != null) opened_details.classList.remove('expanded');
                        div.classList.toggle('expanded');
                        opened_details = div;
                    })
                })
                // Like/Dislike events
                res['hits'].forEach((data) => {
                    if (data['author_id'] === parseInt(getUserId(), 10)) return;

                    let link_id = data['id'];
                    let link = section.querySelector('li.link[data-link-id="' + link_id + '"]');
                    let like = link.querySelector('button.like');
                    let dislike = link.querySelector('button.dislike');
                    like.addEventListener('click', (e) => {
                        let is_liked = like.classList.contains('selected');
                        let is_disliked = dislike.classList.contains('selected');
                        if (is_liked) {
                            updateLikeInfos(link_id, is_liked, is_disliked, 0)
                        } else {
                            updateLikeInfos(link_id, is_liked, is_disliked, 1)
                        }
                    })
                    dislike.addEventListener('click', (e) => {
                        let is_liked = like.classList.contains('selected');
                        let is_disliked = dislike.classList.contains('selected');
                        if (is_disliked) {
                            updateLikeInfos(link_id, is_liked, is_disliked, 0)
                        } else {
                            updateLikeInfos(link_id, is_liked, is_disliked, -1)
                        }
                    })
                })

                section.querySelectorAll('button.delete').forEach((a) => {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (confirm("Êtes-vous sûr de vouloir supprimer ce lien ?\nCette action est irréversible.")) {
                            redirectWithPost(getRootPath() + 'link/manage/delete_link', {
                                id: a.dataset.linkId,
                                r: '?q=' + last_query,
                            });
                        }
                    });
                });
                section.querySelectorAll('button.edit').forEach((a) => {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        show_edit_link_form(a)
                    });
                });

                if (!res['last_offset']) {
                    add_more_button(query, offset + 1);
                }

            } else if (res['status'] === 'invalid_csrf') {
                location.reload();
            }

        })
}

function getLinkHtml(data) {
    let is_own = data['author_id'] === parseInt(getUserId(), 10);
    let own_actions = ''
    if (is_own) {
        own_actions = `
            <button class="edit show-if-opened" data-link-id="` + data['id'] + `"
                data-link-title="` + data['title'] + `" data-link-description="` + data['description'] + `"
                data-link-expiration-date="` + data['expiration_date'] + `" data-link-link="` + data['link'] + `">
                <img src="` + getRootPath() + `svg/edit.svg" alt="Modifier">
            </button>
            <button class="delete show-if-opened" data-link-id="` + data['id'] + `">
                <img src="` + getRootPath() + `svg/delete.svg" alt="Supprimer">
            </button>`
    }

    let expiration_html = ''
    if (data['expiration_date'] !== null) {
        expiration_html = '<span>Expire le ' + formatDateFr(data['expiration_date']) + '</span>';
    }
    let html = `
        <h2>` + data['title'] + `</h2>
        <hr>
        <p class="description">` + out(data['description']) + `</p>
        ` + expiration_html + `
        <div class="details">
            <a target="_blank" href="` + out(data['link']) + `">` + out(data['link']) + `</a>
            <div class="mutable-content">
                <button class="more-button">Détails</button>
                <p class="author">` + out(data['author_name']) + `</p>
            </div>
            <div class="actions">
                <button class="rating like` + (data['is_liked'] ? ' selected' : '') + (is_own ? ' disabled' : '') + `">
                    <img src="` + getRootPath() + `svg/like.svg" alt="J'aime">
                    <p>` + data['likes'] + `</p>
                </button>
                <button class="rating dislike` + (data['is_disliked'] ? ' selected' : '') + (is_own ? ' disabled' : '') + `">
                    <img src="` + getRootPath() + `svg/dislike.svg" alt="Je n'aime pas">
                    <p>` + data['dislikes'] + `</p>
                </button>
                ` + own_actions + `
            </div>
        </div>
    `
    return '<li class="link" data-link-id="' + data['id'] + '">' + html + '</li>';
}

/**
 * @param link_id id of the link
 * @param is_liked Whether the link is already liked or not
 * @param is_disliked Whether the link is already disliked or not
 * @param type 0 for nothing, 1 for like and -1 for dislike
 */
function updateLikeInfos(link_id, is_liked, is_disliked, type) {
    let link = section.querySelector('li.link[data-link-id="' + link_id + '"]');
    let like = link.querySelector('button.like');
    let dislike = link.querySelector('button.dislike');

    let like_invert = type === 1 || is_liked;
    let dislike_invert = type === -1 || is_disliked;

    if (like_invert) {
        like.classList.toggle('loading');
    }
    if (dislike_invert) {
        dislike.classList.toggle('loading');
    }


    fetch(getRootPath() + 'link/jsapi/like', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({"link_id": link_id, 'user_id': getUserId(), "csrf_js": getCsrfToken(), "type": type})
    })
        .then(response => {
            like.classList.remove('loading');
            dislike.classList.remove('loading');
            return response.json()
        })
        .catch(error => {
            like.classList.remove('loading');
            dislike.classList.remove('loading');
        })
        .then(res => {
            if (res['status'] === 'done') {
                if (like_invert) {
                    like.classList.toggle('selected');
                    let text = like.querySelector('p');
                    text.innerText = parseInt(text.innerText, 10) + (is_liked ? -1 : 1);
                }
                if (dislike_invert) {
                    dislike.classList.toggle('selected');
                    let text = dislike.querySelector('p');
                    text.innerText = parseInt(text.innerText, 10) + (is_disliked ? -1 : 1);
                }
            } else if (res['status'] === 'invalid_csrf') {
                location.reload();
            }
        })
}
function hide_edit_link_form() {
    if(editing_link_el != null){
        editing_link_el.classList.remove('hidden');
        editing_link_el.nextElementSibling.remove();
        editing_link_el = null;
    }
}
function show_edit_link_form(button_el) {
    let id = button_el.dataset.linkId;
    let title = button_el.dataset.linkTitle;
    let description = button_el.dataset.linkDescription;
    let expiration_date = button_el.dataset.linkExpirationDate;
    let url = button_el.dataset.linkLink;

    if (opened_details != null) opened_details.classList.remove('expanded');
    if (editing_link_el != null) hide_edit_link_form()

    editing_link_el = section.querySelector('li.link[data-link-id="' + id + '"]');
    editing_link_el.classList.add('hidden');

    let html = `
        <li class="edit-link">
            <form class="add-link" method="post" action="` + getRootPath() + `link/manage/edit_link">
                <input type="hidden" name="id" value="` + id + `">
                <input type="hidden" name="csrf_js" value="` + getCsrfToken() + `">
                <input type="hidden" name="r" value="?q=` + last_query + `">
                <p>Modifier un lien entraîne la réinitialisation des compteurs de like/dislike.</p>
                <div class="header">
                    <input type="text" name="title" placeholder="Titre" maxlength="50" required
                           value="` + out(title) + `">
                    <textarea name="description" rows="4" placeholder="Description" maxlength="1000">` + out(description) + `</textarea>
                </div>
                <div class="horizontal">
                    <label for="expiration_date">Date d'expiration (facultatif)</label>
                    <input type="date" id="expiration_date" name="expiration_date"
                           value="` + out(expiration_date) + `" min="` + date_tomorrow_formatted() + `">
                </div>
                <div class="horizontal">
                    <input type="url" name="url" value="` + url + `" placeholder="Lien (URL)" maxlength="2048" required>
                    <input class="fixed" type="submit" value="Annuler" onclick="hide_edit_link_form()">
                    <input class="fixed" type="submit" name="submit" value="Modifier">
                </div>
            </form>
        </li>`;

    editing_link_el.insertAdjacentHTML('afterend', html);
}

function add_more_button(query, offset) {
    let more_button = '<button id="more-button" onclick="updateLinks(\'' + query + '\', ' + offset + ')">Plus de résultats</button>';
    section.innerHTML += more_button;
}

function date_tomorrow_formatted() {
    let tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return formatDate(tomorrow)
}




