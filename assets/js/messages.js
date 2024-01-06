document.addEventListener("DOMContentLoaded", () => {
    let messages = document.querySelectorAll('[data-message]');
    console.debug(messages);
    messages.forEach((message) => {
        console.debug(message);
        let button = message.querySelector('.close');
        button.addEventListener('click', (event) => {
            message.remove();
        });
    });
});