let input = document.getElementById("subject-input");
let list = document.getElementById("subject-list");

input.onfocus = function () {
    list.style.display = 'block';
};
input.addEventListener('focusout', () => {
    list.style.display = 'none';
});


let currentFocus;
for (let i = 0; i < list.options.length; i++) {
    list.options[i].onmousedown = function () {
        currentFocus = i;
        addActive(list.options);
        input.value = list.options[i].value;
        list.style.display = 'none';
        input.style.borderRadius = "5px";
    }
}

input.oninput = function () {
    currentFocus = -1;
    const text = input.value;
    for(let option of list.options) {
        console.log(option)
        if(option.className === 'custom'){
            option.remove();
            list.innerHTML += '<option class="custom" value="' + text + '">+ Ajouter ' + text + '</option>';
        }else if (option.value.toLowerCase().indexOf(text.toLowerCase()) > -1) {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    }
}
currentFocus = -1;
input.onkeydown = function (e) {
    if (e.keyCode === 40) { // down
        currentFocus++
        addActive(list.options);
    } else if (e.keyCode === 38) { // up
        currentFocus--
        addActive(list.options);
    } else if (e.keyCode === 13) { // enter
        e.preventDefault();
        if (currentFocus > -1) {
            if (list.options) list.options[currentFocus].onmousedown();
        }
    }
}

function addActive(options) {
    if (!options) return false;
    removeActive(options);
    if (currentFocus >= options.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (options.length - 1);
    options[currentFocus].classList.add("active");
}

function removeActive(options) {
    for (let i = 0; i < options.length; i++) {
        options[i].classList.remove("active");
    }
}