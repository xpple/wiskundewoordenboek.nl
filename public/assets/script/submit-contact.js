/**
 * @type {HTMLFormElement}
 */
const messageForm = document.getElementById("message-form");
const messageOutput = document.getElementById("message-output");

messageForm.addEventListener('submit', async e => {
    e.preventDefault();

    messageOutput.removeAttribute('class');

    const formData = new FormData(messageForm);

    const response = await fetch('', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        messageOutput.innerHTML = "Er ging iets fout met het indienen!";
        messageOutput.classList.add("failure");
        return;
    }
    const json = await response.json();
    if (json['success'] !== true) {
        messageOutput.innerHTML = json['errorMessage'];
        messageOutput.classList.add("failure");
        return;
    }

    messageForm.reset();
    messageOutput.innerHTML = "Bericht succesvol verstuurd!";
    messageOutput.classList.add("success");
});
