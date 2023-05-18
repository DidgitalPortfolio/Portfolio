const fadeIn = (el, timeout, display) => {
    el.style.opacity = 0;
    el.style.display = display || 'block';
    el.style.transition = `opacity ${timeout}ms`;
    setTimeout(() => {
        el.style.opacity = 1;
    }, 10);
};

const fadeOut = (el, timeout) => {
    el.style.opacity = 1;
    el.style.transition = `opacity ${timeout}ms`;
    el.style.opacity = 0;
    setTimeout(() => {
        el.style.display = 'none';
    }, timeout);
};

// Модальное окно
function bindModal(trigger, modal, close = null) {
    const triggers = document.querySelectorAll(trigger);
    if (!triggers || triggers.length === 0) return;
    modal = document.querySelector(modal);
    close = document.querySelector(close);

    [...triggers].forEach(item => {
        item.addEventListener('click', e => {
            e.preventDefault();
            fadeIn(modal, 500, 'flex');
            body.classList.add('locked');
        });
    });

    const body = document.body;

    if (close) {
        close.addEventListener('click', () => {
            fadeOut(modal, 500);
            body.classList.remove('locked');
        });
    }
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            fadeOut(modal, 500);
            body.classList.remove('locked');
        }
    })
}

bindModal('.js-modal', '.modal__wrapper');

// Отправка формы

const checkName = (u) => {
    let valid = false;
    const min = 2,
        max = 25;
    const username = u.value.trim();
    if (!isRequired(username)) {
        showError(u, 'Имя не может быть пустым.');
    } else if (!isBetween(username.length, min, max)) {
        showError(u, `Имя должно быть больше 2 и меньше 25 букв.`)
    } else if (!isNameValid(username)) {
        showError(u, `Имя может содержать только буквы.`)
    } else {
        showSuccess(u);
        valid = true;
    }
    return valid;
};

const checkUsername = (u) => {
    let valid = false;
    const min = 2,
        max = 25;
    const username = u.value.trim();
    if (!isRequired(username)) {
        showError(u, 'Логин не может быть пустым.');
    } else if (!isBetween(username.length, min, max)) {
        showError(u, `Логин должен быть больше 2 и меньше 25 символов.`)
    } else if (!isUserNameValid(username)) {
        showError(u, `Логин может содержать только буквы и цифры.`)
    } else {
        showSuccess(u);
        valid = true;
    }
    return valid;
};

const checkPassword = (p) => {
    let valid = false;
    const min = 8,
        max = 25;
    const password = p.value.trim();
    if (!isRequired(password)) {
        showError(p, 'Пароль не может быть пустым.');
    } else if (!isBetween(password.length, min, max)) {
        showError(p, `Пароль должен быть больше 7 символов.`)
    } else {
        showSuccess(p);
        valid = true;
    }
    return valid;
};

const checkEmail = (e) => {
    let valid = false;
    const email = e.value.trim();
    if (!isRequired(email)) {
        showError(e, 'Email не может быть пустым.');
    } else if (!isEmailValid(email)) {
        showError(e, 'Email имеет неверный формат.')
    } else {
        showSuccess(e);
        valid = true;
    }
    return valid;
};

const isEmailValid = (email) => {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
};

const isNameValid = (name) => {
    const re = /^([А-яЁё A-Za-z]{2,25})$/;
    return re.test(name);
}

const isUserNameValid = (name) => {
    const re = /^([А-яЁё A-Za-z 0-9]{2,25})$/;
    return re.test(name);
}

const isRequired = value => value === '' ? false : true;
const isBetween = (length, min, max) => length < min || length > max ? false : true;

const showError = (input, message) => {
    const formField = input.parentElement;
    formField.classList.remove('success');
    formField.classList.add('error');
    const error = formField.querySelector('small');
    error.textContent = message;
};

const showSuccess = (input) => {
    const formField = input.parentElement;
    formField.classList.remove('error');
    formField.classList.add('success');
    const error = formField.querySelector('small');
    error.textContent = '';
}

const clearInputs = (item) => {
    const labels = item.querySelectorAll('label.success');
    [...labels].forEach(label => {
        label.classList.remove('success');
    });
}

const showErrorResult = (result_el, message) => {
    result_el.classList.remove('success');
    result_el.classList.add('error');
    result_el.textContent = message;
}

const showSuccessResult = (result_el) => {
    result_el.classList.remove('error');
    result_el.classList.add('success');
    result_el.textContent = 'Успешная регистрация!';
}

const debounce = (fn, delay = 500) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        timeoutId = setTimeout(() => {
            fn.apply(null, args)
        }, delay);
    };
};

const form = document.querySelectorAll('form.js-main-form');

[...form].forEach(item => {
    const name = item.querySelector('.name-input');
    const username = item.querySelector('.username-input');
    const email = item.querySelector('.email-input');
    const password = item.querySelector('.password-input');

    const result_text = item.querySelector('.form-result');

    item.addEventListener('submit', function (e) {
        e.preventDefault();

        let isNameValid = checkName(name),
            isUsernameValid = checkUsername(username),
            isEmailValid = checkEmail(email),
            isPasswordValid = checkPassword(password);

        let isFormValid = isNameValid &&
            isUsernameValid &&
            isEmailValid &&
            isPasswordValid;

        if (isFormValid) {
            fetch('/action/register', {
                method: 'POST',
                body: new FormData(item)
            })
                .then(response => response.json())
                .then(
                    result => {
                        if (result.status == 'ok') {
                            item.reset();
                            clearInputs(item);
                            showSuccessResult(result_text);
                            setInterval(() => {
                                let modal = document.querySelector('.modal__wrapper');
                                fadeOut(modal, 500);
                                document.body.classList.remove('locked');
                            }, 1500);
                        } else {
                            result.text = result.text ?? 'Ошибка!';
                            showErrorResult(result_text, result.text);
                        }
                    }
                );
        }
    });

    item.addEventListener('input', debounce(function (e) {
        switch (e.target.name) {
            case 'name':
                checkName(e.target);
                break;
            case 'username':
                checkUsername(e.target);
                break;
            case 'email':
                checkEmail(e.target);
                break;
            case 'password':
                checkPassword(e.target);
                break;
        }
    }));
});


// Смена фотографий и комментариев
const blockPhotos = document.querySelector('.next-photos');
const blockComments = document.querySelector('.comments');
const btn = document.querySelector('#com-js');
const closeComments = document.querySelector('.comments__close');

let flag = true;

if (btn) {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (!flag) {
            fadeOut(blockComments, 500);
            setTimeout(function () {
                fadeIn(blockPhotos, 500, 'block');
            }, 400);
            flag = true;
        } else {
            fadeOut(blockPhotos, 500);
            setTimeout(function () {
                fadeIn(blockComments, 500, 'block');
            }, 400);
            flag = false;
        }
    });
}

if (closeComments) {
    closeComments.addEventListener('click', () => {
        fadeOut(blockComments, 500);
        setTimeout(function () {
            fadeIn(blockPhotos, 500, 'block');
        }, 400);
        flag = true;
    });
}

// Отправка формы с картинкой

const imageFile = document.querySelector('.modal-post__image');
if (imageFile) {
    imageFile.addEventListener("change", (e) => {
        let files = e.target.files;
        let totalSize = 0;
        document.getElementById('previews').innerHTML = '';

        for (let i = 0, f; f = files[i]; i++) {
            if (!f.type.match('image.*')) {
                alert("Image only please....");
            }

            let { name: fileName, size } = f;
            let fileSize = +(size / 1000).toFixed(2);

            totalSize += fileSize;

            let reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    let div = document.createElement('div');
                    div.innerHTML = ['<img class="thumb" title="', theFile.name, '" src="', e.target.result, '" />'].join('');
                    document.getElementById('previews').insertBefore(div, null);
                };
            })(f);
            reader.readAsDataURL(f);
        }

        const fileNameAndSize = `Выбрано файлов: ${files.length} - ${totalSize}KB`;
        document.querySelector('.modal-post__span').textContent = fileNameAndSize;
    });
}

bindModal('.js-modal-img', '.modal__wrapper', '.modal-post__cancel');

document.addEventListener('DOMContentLoaded', init, false);

let fileField, statusDiv;

async function init() {
    const uploadImages = document.querySelector('#uploadImages');
    if (uploadImages) {
        fileField = imageFile;
        statusDiv = document.querySelector('.modal-post__message');
        document.querySelector('#uploadImages').addEventListener('click', doUpload, false);
    }
}

async function doUpload(e) {
    e.preventDefault();
    statusDiv.innerHTML = '';

    let nameInput = document.getElementById('name');
    let descInput = document.getElementById('description');
    let totalFilesToUpload = fileField.files.length;

    if (nameInput.value.length == 0) {
        statusDiv.innerHTML = 'Name is empty!';
        return;
    }

    if (descInput.value.length == 0) {
        statusDiv.innerHTML = 'Description is empty!';
        return;
    }

    if (totalFilesToUpload === 0) {
        statusDiv.innerHTML = 'Please select one or more files.';
        return;
    }

    let projectAnswer = await createProject();

    if (projectAnswer['status'] == 'ok') {

        statusDiv.innerHTML = `Uploading ${totalFilesToUpload} files.`;

        let uploads = [];
        for (let i = 0; i < totalFilesToUpload; i++) {
            uploads.push(uploadFile(fileField.files[i], projectAnswer['id']));
        }

        await Promise.all(uploads);

        statusDiv.innerHTML = 'All complete.';

        updateProfileImages();

        nameInput.value = '';
        descInput.value = '';
        fileField.value = '';

        const previews = document.getElementById("previews");
        previews.innerHTML = '';
        document.querySelector('.modal-post__span').textContent = 'Tap and drop media';

    } else {
        statusDiv.innerHTML = 'Something went wrong';
        return;
    }
}

async function uploadFile(f, id) {
    console.log(`Starting with ${f.name}`);
    let form = new FormData();
    form.append('project_id', id);
    form.append('file', f);
    let resp = await fetch('/action/image/upload', { method: 'POST', body: form });
    let data = await resp.json();
    console.log(`Done with ${f.name}`);
    return data;
}

async function createProject() {
    console.log(`Create project`);
    let form = new FormData();

    let nameValue = document.getElementById('name').value
    let descValue = document.getElementById('description').value

    form.append('name', nameValue);
    form.append('description', descValue);

    let resp = await fetch('/action/project/create', { method: 'POST', body: form });
    let data = await resp.json();
    return data;
}

async function updateProfileImages() {
    let resp = await fetch('/action/project', { method: 'GET' });
    let data = await resp.text();
    const updateElement = document.querySelector(".profile-images__list");
    updateElement.innerHTML = data;
}

// Слайдер

let images = document.querySelectorAll(".next-photos__image");

images.forEach(function (elem) {
    elem.addEventListener("click", function (e) {
        e.preventDefault();
        let mainImg = document.getElementById('main-img');
        let img = e.target.closest('img');
        if (img) {
            if (img.src == mainImg.src)
                return;
            mainImg.src = img.src;

            document.getElementById('picture-anchor').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            })
        }

    });
});

$(document).ready(function () {
    $('.next-photos__images').slick({
        dots: false,
        infinite: true,
        arrows: true,
        draggable: false,
        variableWidth: true,
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 1,
    });
});


// Добавление комментария

const checkComment = (c) => {
    let valid = false;
    const min = 2,
        max = 110;
    const comment = c.value.trim();
    if (!isRequired(comment)) {
        alert('Комментарий не может быть пустым.');
    } else if (!isBetween(comment.length, min, max)) {
        alert(`Комментарий должен быть больше 2 и меньше 110 букв.`);
    } else {
        valid = true;
    }
    return valid;
};

const commentForm = document.querySelector('.comments__form');
if (commentForm) {
    commentForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const commentText = commentForm.querySelector('.comments__input');

        let isCommentValid = checkComment(commentText);

        if (isCommentValid) {
            fetch('/action/comment', {
                method: 'POST',
                body: new FormData(commentForm)
            })
                .then(response => response.json())
                .then(
                    result => {
                        if (result.status == 'ok') {
                            commentForm.reset();
                            const div = document.createElement('div');

                            div.className = 'comment';
                            div.innerHTML = `
                            <div class="comment__img">
                                <img src="${result.avatar}" alt="" class="comment__image">
                            </div>
                            <div class="comment__desc">
                                <div class="comment__author">
                                    ${result.author}
                                </div>
                                <div class="comment__text">
                                    ${result.comment}
                                </div>
                            </div>
                            `;

                            document.querySelector('.comments__list').appendChild(div);
                        } else {
                            alert('Ошибка добавления комментария!')
                        }
                    }
                );
        }

    });
}

// Загрузка аватарки

const imageProfile = document.querySelector('.profile-top__image-input');
if (imageProfile) {
    imageProfile.addEventListener("change", async (e) => {
        let file = e.target.files[0];
        console.log(file);

        if (!file.type.match('image.*')) {
            alert("Image only please....");
            return;
        }

        let profileAnswer = await uploadProfileImage(file);

        if (profileAnswer['status'] == 'ok') {
            const avatar = document.querySelector('.profile-top__image');
            avatar.src = '/uploads/avatars/' + profileAnswer['avatar'];
        } else {
            alert('Ошибка загрузки аватарки!')
            return;
        }

    });
}

async function uploadProfileImage(f) {
    console.log(`Starting with ${f.name}`);
    let form = new FormData();
    form.append('file', f);
    let resp = await fetch('/action/avatar/upload', { method: 'POST', body: form });
    let data = await resp.json();
    console.log(`Done with ${f.name}`);
    return data;
}

// Лайк

const likeButton = document.querySelector('#like-js');

if (likeButton) {
    likeButton.addEventListener("click", (e) => {
        e.preventDefault();
        const likeImage = likeButton.querySelector('img');
        let form = new FormData();
        form.append('like', 1);
        form.append('project_id', likeButton.dataset.project);

        fetch('/action/like', {
            method: 'POST',
            body: form
        })
            .then(response => response.json())
            .then(
                result => {
                    if (result.status == 'ok') {
                        if (result.like == 1) {
                            likeImage.src = '/img/icon-like-yes.png'
                        } else {
                            likeImage.src = '/img/icon-like.png'
                        }
                    } else {
                        alert('Ошибка добавления лайка!')
                    }
                }
            );

    });
}

// Модальное окно socials

bindModal('.js-modal-socials', '.modal-socials', '.modal-socials__cancel');

// Форма socials

let statusDivSocial = document.querySelector('.modal-socials__message');

const checkSocials = (s) => {
    let valid = false;
    const min = 0,
        max = 33;
    const social = s.value.trim();

    if (isRequired(social)) {
        if (!isBetween(social.length, min, max)) {
            statusDivSocial.innerHTML = 'Ссылка должна быть меньше 33 букв.';
        } else if (!social.startsWith("https://")) {
            statusDivSocial.innerHTML = 'Ссылка должна начинаться с https://';
        } else {
            valid = true;
        }
    } else {
        statusDivSocial.innerHTML = 'Ссылка не может быть пустой.';
    }
    return valid;
};

const saveSocials = document.querySelector('#uploadSocials');

if (saveSocials) {
    saveSocials.addEventListener('click', function (e) {
        e.preventDefault();
        const socialsForm = document.querySelector('.modal-socials__form');
        const socialsVkText = socialsForm.querySelector('#social-vk');
        const socialsTgText = socialsForm.querySelector('#social-tg');

        let isVkValid = checkSocials(socialsVkText);
        let isTgValid = checkSocials(socialsTgText);

        let isFormValid = isVkValid && isTgValid;

        if (isFormValid) {
            fetch('/action/socials', {
                method: 'POST',
                body: new FormData(socialsForm)
            })
                .then(response => response.json())
                .then(
                    result => {
                        if (result.status == 'ok') {
                            statusDivSocial.innerHTML = 'Успешно обновлено!';
                        } else {
                            alert('Ошибка добавления комментария!')
                        }
                    }
                );
        }
    });
}