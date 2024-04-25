/**
 * @type {HTMLFormElement}
 */
const createPageForm = document.getElementById("create-page-form");
const createPageOutput = document.getElementById("create-page-output");

createPageForm.addEventListener('submit', async e => {
    e.preventDefault();

    createPageOutput.removeAttribute('class');

    const formData = new FormData(createPageForm);

    const response = await fetch('', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        createPageOutput.innerHTML = "Er ging iets fout met het indienen!";
        createPageOutput.classList.add("failure");
        return;
    }
    const json = await response.json();
    if (json['success'] !== true) {
        createPageOutput.innerHTML = json['errorMessage'];
        createPageOutput.classList.add("failure");
        return;
    }

    createPageForm.reset();
    createPageOutput.innerHTML = "Nieuw woord succesvol ingediend!";
    createPageOutput.classList.add("success");
});
