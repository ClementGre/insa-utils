document.querySelectorAll('.dropdown').forEach(function (dropdown) {
    dropdown.addEventListener('click', function (event) {
        event.stopPropagation();

        document.querySelectorAll('.dropdown').forEach(function (dropdown2) {
            if(dropdown2 !== dropdown) dropdown2.classList.remove('showing');
        });

        dropdown.classList.toggle('showing');
    });
});
window.onclick = function (event) {
    if (!event.target.matches('.dropdown')) {
        document.querySelectorAll('.dropdown').forEach(function (dropdown) {
            dropdown.classList.remove('showing');
        });
    }
}
function getRootPath() {
    return document.querySelector('div.root-path-container').dataset.rootPath;
}
function getCsrfToken() {
    return document.querySelector('div.csrf-container').dataset.csrf;
}
function getUserId() {
    return document.querySelector('div.user-id-container').dataset.userId;
}

function prettyPrintJSONtoHTML(json) {
    return JSON.stringify(json, function(k, v){
        let doStringify = false;
        if(v instanceof Array && v.every(a => !(a instanceof Object || a instanceof Array) || a instanceof Date)){
            doStringify = true;

        }else if(v instanceof Object && Object.values(v).every(a => !(a instanceof Object || a instanceof Array) || a instanceof Date )){
            doStringify = true;
        }
        if (doStringify){
            return JSON.stringify(v)
                .replace(/\\"/g, "\"")
                .replace(/","/g, "\", \"")
                .replace(/":"/g, "\": \"")
        }
        return v;
    }, 4)
        .replace(/\\/g, '')
        .replace(/\"\[/g, '[')
        .replace(/\]\"/g,']')
        .replace(/\"\{/g, '{')
        .replace(/\}\"/g,'}')
        .replace(/\n/g, "<br>")
        .replace(/    /g, "&nbsp;&nbsp;")
}

function createElementFromHTML(htmlString) {
    const div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild;
}

function redirectWithPost(url, data) {
    const $form = document.createElement('form');
    $form.method = 'post';
    $form.action = url;
    data['csrf_js'] = getCsrfToken();
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            const $input = document.createElement('input');
            $input.type = 'hidden';
            $input.name = key;
            $input.value = data[key];
            $form.appendChild($input);
        }
    }
    document.body.appendChild($form);
    $form.submit();
}

function formatDate(date) {
    let dd = date.getDate();
    let mm = date.getMonth() + 1;
    const yyyy = date.getFullYear();
    if (dd < 10) {
        dd = '0' + dd;
    }
    if (mm < 10) {
        mm = '0' + mm;
    }
    return yyyy + '-' + mm + '-' + dd
}

function formatDateFr(date_str) {
    let date = new Date(Date.parse(date_str.replace(/-/g, '/')));
    let dd = date.getDate();
    let mm = date.getMonth() + 1;
    const yyyy = date.getFullYear();
    if (dd < 10) {
        dd = '0' + dd;
    }
    if (mm < 10) {
        mm = '0' + mm;
    }
    return dd + '/' + mm + '/' + yyyy
}

function out(text) {
    if(!text) return text;

    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function (m) {
        return map[m];
    });
}
