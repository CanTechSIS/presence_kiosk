class Ajax {
    static get(url) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        resolve(JSON.parse(xhr.responseText));
                    } else {
                        reject(new Error(`Request failed with status ${xhr.status}`));
                    }
                }
            };
            xhr.send();
        });
    }

    static post(url, data) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        resolve(JSON.parse(xhr.responseText));
                    } else {
                        reject(new Error(`Request failed with status ${xhr.status}`));
                    }
                }
            };
            xhr.send(JSON.stringify(data));
        });
    }
}

class Modal {
    constructor(modal, reload = false ) {
        this.modal = modal;
        this.reload = reload;
        this.modal.innerHTML = `
            <div class="modal-content">
            <span class="close">&times;</span>
            <p id="modalMessage"></p>
            </div>
        `;
        this.close = this.modal.querySelector('.close');
        this.attachEventListeners();
    }

    closeModal( ) {
        this.modal.style.display = 'none';
        if (this.reload === true) {
            location.reload();
        }
    }

    attachEventListeners() {
        this.close.addEventListener('click', () => this.closeModal());
        this.modal.addEventListener('click', () => this.closeModal());
    }

    openModal(message) {
        this.modal.querySelector('#modalMessage').innerHTML = message;
        this.modal.style.display = 'block';
    }

}

// Initialize modals
document.addEventListener('DOMContentLoaded', () => {
    let modals = document.getElementsByClassName('modal');
    let closes = document.getElementsByClassName('close');
    for (let i = 0; i < modals.length; i++) {
        new Modal(modals[i], closes[i]);
    }
});
