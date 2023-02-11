document.querySelectorAll('.dropdown').forEach(function (dropdown) {
    dropdown.addEventListener('click', function (event) {
        event.stopPropagation();
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