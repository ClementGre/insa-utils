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

document.addEventListener("visibilitychange", function() {
    if (!document.hidden){
        // check csrf is still valid
        fetch(getRootPath() + 'agenda/jsapi/checkcsrf', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({"csrf_name": 'js', "csrf_value": getCsrfToken()})
        })
            .then(response => {
                return response.json()
            })
            .catch(error => {
                location.reload();
            })
            .then(res => {
                if(res['status'] !== 'success'){
                    location.reload();
                }
            })
    }
});

function getCsrfToken() {
    return document.querySelector('div.csrf-container').dataset.csrf;
}