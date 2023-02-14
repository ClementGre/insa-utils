let deletingTodos = [];

document.querySelectorAll('.delete-todo').forEach((a) => {
    a.addEventListener('click', (e) => {
        e.preventDefault();
        closeDeletingTodos();
        const todoId = a.dataset.todoId;
        const todo = document.querySelector(`.todo[data-todo-id="${todoId}"]`);
        openDeletingTodo(todoId, todo);
    });
});

function closeDeletingTodos() {
    deletingTodos.forEach((deletingTodo) => {
        deletingTodo.todo.classList.remove('hidden');
        deletingTodo.form.remove();
    });
    deletingTodos = [];
}
function openDeletingTodo(todoId, todo){
    todo.classList.add('hidden');
    let form = getTodoDeletionConfirmation(todoId);
    todo.parentNode.insertBefore(form, todo.nextSibling);

    deletingTodos.push({todo: todo, form: form})

    const cancelButton = document.querySelector(`.todo.delete-todo[data-detete-todo-id="${todoId}"] input[type="button"]`);
    cancelButton.addEventListener('click', (e) => {
        e.preventDefault();
        closeDeletingTodos();
    });
}

function getTodoDeletionConfirmation(todoId) {
    const $html = `
        <div class="todo delete-todo" data-detete-todo-id="${todoId}">
            <form action="${getRootPath()}todo/" method="post">
                <p>Confirmer la suppression de cette tâche ?</p>
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" name="id" value="${todoId}"/>
                <div>
                    <input type="submit" value="Supprimer">
                    <input type="button" value="Annuler">
                </div>
            </form>
        </div>
        `;
    return createElementFromHTML($html);
}

function createElementFromHTML(htmlString) {
    const div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild;
}
function getRootPath(){
    return document.querySelector('div.root-path-container').dataset.rootPath;
}