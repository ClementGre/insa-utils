document.querySelectorAll('.delete-todo').forEach((a) => {
    a.addEventListener('click', (e) => {
        console.log('clicked');
        e.preventDefault();
        const todoId = a.dataset.todoId;
        const todo = document.querySelector(`.todo[data-todo-id="${todoId}"]`);
        todo.classList.add('hidden');
        todo.parentNode.insertBefore(getTodoDeletionConfirmation(todoId), todo.nextSibling);


        const cancelButton = document.querySelector(`.todo.detete-todo[data-detete-todo-id="${todoId}"] input[type="button"]`);
        cancelButton.addEventListener('click', (e) => {
            e.preventDefault();
            todo.classList.remove('hidden');
            document.querySelector(`.todo.detete-todo[data-detete-todo-id="${todoId}"]`).remove();
        });

    });
});

function createElementFromHTML(htmlString) {
    const div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild;
}

function getTodoDeletionConfirmation($todoId) {
    const $html = `
        <div class="todo detete-todo" data-detete-todo-id="` + $todoId + `">
            <form action="">
                <p>Confirmer la suppression de cette tâche ?</p>
                
                <input type="hidden" name="action" value="delete"/>
                <div>
                    <input type="submit" value="Supprimer">
                    <input type="button" value="Annuler">
                </div>
            </form>
        </div>
        `;
    
    return createElementFromHTML($html);
}