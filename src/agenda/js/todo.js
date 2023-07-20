let deletingTodos = [];
let editingTodos = [];

document.querySelectorAll('.delete-todo').forEach((a) => {
    a.addEventListener('click', (e) => {
        e.preventDefault();
        closeEditingTodos();
        closeDeletingTodos();
        const todoId = a.dataset.todoId;
        const todo = document.querySelector(`.todo[data-todo-id="${todoId}"]`);
        openDeletingTodo(todoId, todo);
    });
});

document.querySelectorAll('.edit-todo').forEach((a) => {
    a.addEventListener('click', (e) => {
        e.preventDefault();
        closeEditingTodos();
        closeDeletingTodos();
        const todoId = a.dataset.todoId;
        const todo = document.querySelector(`.todo[data-todo-id="${todoId}"]`);
        openEditingTodo(todoId, todo, e.target);
    });
});
document.querySelectorAll('.make-public-todo').forEach((a) => {
    a.addEventListener('click', (e) => {
        e.preventDefault();
        closeEditingTodos();
        closeDeletingTodos();
        const todoId = a.dataset.todoId;
        redirectWithPost(getRootPath() + 'agenda/manage/todo', {
            action: 'make_public',
            id: todoId,
            csrf_js: getCsrfToken()
        });
    });
});

document.querySelectorAll('.todo .heading .status').forEach((div) => {
    const p = div.firstElementChild;
    p.addEventListener('click', (e) => {
        if (p.classList.contains('loading') || div.classList.contains('reminder')) {
            return;
        }
        e.preventDefault();
        const todoId = p.dataset.todoId;
        p.classList.add('loading');

        let status = 'done';
        div.classList.forEach((className) => {
            if (className === 'todo' || className === 'in-progress' || className === 'done') {
                status = className;
            }
        });
        let newStatus = status === 'todo' ? 'in-progress' : (status === 'in-progress' ? 'done' : 'todo');
        let newText = newStatus === 'todo' ? 'À faire' : (newStatus === 'in-progress' ? 'En cours' : 'Fait');

        fetch(getRootPath() + 'agenda/jsapi/status', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({"todo_id": todoId, 'user_id': getUserId(), "csrf_js": getCsrfToken()})
        })
            .then(response => {
                p.classList.remove('loading');
                return response.json()
            })
            .catch(error => {
                p.classList.remove('loading');
            })
            .then(res => {
                if(res['status'] === 'done'){
                    div.classList.remove(status);
                    div.classList.add(newStatus);
                    p.firstChild.textContent = newText;
                }else if (res['status'] === 'invalid_csrf'){
                    location.reload();
                }
            })
    });
});

function closeDeletingTodos() {
    deletingTodos.forEach((deletingTodo) => {
        deletingTodo.todo.classList.remove('hidden');
        deletingTodo.form.remove();
    });
    deletingTodos = [];
}

function closeEditingTodos() {
    editingTodos.forEach((editingTodos) => {
        editingTodos.todo.classList.remove('hidden');
        editingTodos.form.remove();
    });
    editingTodos = [];
}

function openDeletingTodo(todoId, todo) {
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

function openEditingTodo(todoId, todo, target) {
    todo.classList.add('hidden');
    let form = getTodoEditForm(todoId, target.dataset.subjectId, target.dataset.duedate, target.dataset.type, target.dataset.content, target.dataset.link);
    todo.parentNode.insertBefore(form, todo.nextSibling);

    editingTodos.push({todo: todo, form: form})

    const cancelButton = document.querySelector(`.todo.edit-todo[data-edit-todo-id="${todoId}"] input[type="button"]`);
    cancelButton.addEventListener('click', (e) => {
        e.preventDefault();
        closeEditingTodos();
    });
}

function getTodoDeletionConfirmation(todoId) {
    const $html = `
        <div class="todo delete-todo" data-detete-todo-id="${todoId}">
            <form action="${getRootPath()}agenda/manage/todo" method="post">
                <p>Confirmer la suppression de cette tâche ?</p>
                <input type="hidden" name="csrf_js" value="${out(getCsrfToken())}">
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" name="r" value="${getPageName()}"/>
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

function getTodoEditForm(todoId, subject_id, duedate, type, content, link) {

    if (getSubjects().length === 0) {
        return createElementFromHTML('<p class="no-todo">Pour éditer une tâche, ajoutez d\'abord des matières&#8239;:<br><a href="' + getRootPath() + 'agenda/subjects">Ajouter des matières</a></p>');
    }

    const $html = `
        <form class="todo edit-todo" method="post" action="${getRootPath()}agenda/manage/todo" data-edit-todo-id="${todoId}">
            <input type="hidden" name="action" value="edit"/>
            <input type="hidden" name="id" value="${todoId}"/>
            <input type="hidden" name="r" value="${getPageName()}"/>
            <input type="hidden" name="csrf_js" value="${out(getCsrfToken())}">
            <div class="heading">
                <select id="subject" name="subject_id" required onChange="onSubjectComboChange(event);">
                    ${getSubjects().map((s) => {
                return `<option value="${s.id}" ${subject_id == s.id ? 'selected="selected"' : ''}>${out(s.name)}</option>`;
            }).join('')}
                    <option value="manage">Gérer les matières</option>
                </select>
                <input type="date" id="duedate" name="duedate"
                       value="${duedate}" min="${firstPossibleDateString()}" max="${lastPossibleDateString()}" required>
                <select class="fixed" id="type" name="type" required>
                    <option value="report" ${type === 'report' ? 'selected="selected"' : ''}>Rendu</option>
                    <option value="practice" ${type === 'practice' ? 'selected="selected"' : ''}>Exercice</option>
                    <option value="reminder" ${type === 'reminder' ? 'selected="selected"' : ''}>Pense bête</option>
                </select>
            </div>
            <div class="content">
                <textarea name="content" rows="4" placeholder="Titre&#10;Description" maxlength="3000" required>${out(content)}</textarea>
            </div>
            <div class="validate">
                <input type="text" name="link" placeholder="Lien" value="${out(link)}" maxlength="2048">
                <input class="fixed" type="button" name="cancel" value="Annuler">
                <input class="fixed" type="submit" name="submit" value="Éditer">
            </div>
        </form>
        `;
    return createElementFromHTML($html);
}

function redirectWithPost(url, data) {
    const $form = document.createElement('form');
    $form.method = 'post';
    $form.action = url;
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            const $input = document.createElement('input');
            $input.type = 'hidden';
            $input.name = key;
            $input.value = data[key];
            $form.appendChild($input);
        }
    }
    // csrf
    const $input = document.createElement('input');
    $input.type = 'hidden';
    $input.name = 'csrf_js';
    $input.value = getCsrfToken();
    $form.appendChild($input);

    document.body.appendChild($form);
    $form.submit();
}

function getSubjects() {
    return JSON.parse(document.querySelector('div.subjects-container').dataset.subjects);
}

function getPageName() {
    return document.querySelector('div.page-name-container').dataset.pageName;
}

function firstPossibleDateString() {
    if(getPageName() === 'all'){
        const yyyy = new Date().getFullYear();
        if (new Date().getMonth() >= 7) {
            return yyyy + '-09-01'
        }else{
            return (yyyy-1) + '-09-01'
        }
    }else{
        const date = new Date();
        return formatDate(date);
    }
}

function lastPossibleDateString() {
    const yyyy = new Date().getFullYear();
    return yyyy + '-06-30'
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

function onSubjectComboChange(e) {
    if (e.target.value === 'manage') {
        window.location = getRootPath() + 'agenda/subjects'
        e.target.value = null;
    }
}
