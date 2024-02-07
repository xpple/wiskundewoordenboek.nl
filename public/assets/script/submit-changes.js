/**
 * @type {HTMLFormElement}
 */
const makeChangesForm = document.getElementById("make-changes-form") ?? document.getElementById("create-page-form");
const makeChangesOutput = document.getElementById("make-changes-output") ?? document.getElementById("create-page-output");

makeChangesForm.addEventListener('submit', async e => {
    e.preventDefault();

    makeChangesOutput.removeAttribute('class');

    const formData = new FormData(makeChangesForm);

    const response = await fetch('', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        makeChangesOutput.innerHTML = "Er ging iets fout met het indienen!";
        makeChangesOutput.classList.add("failure");
        return;
    }
    const json = await response.json();
    if (json['success'] !== true) {
        makeChangesOutput.innerHTML = json['errorMessage'];
        makeChangesOutput.classList.add("failure");
        return;
    }

    makeChangesForm.reset();
    makeChangesOutput.innerHTML = "Aanpassingen succesvol ingediend!";
    makeChangesOutput.classList.add("success");
});
