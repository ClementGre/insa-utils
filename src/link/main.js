document.querySelectorAll('form input[name="title"]').forEach((a) => {
    // Select the textarea following the input when pressing enter
    a.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            a.nextElementSibling.focus();
        }
    });
});
