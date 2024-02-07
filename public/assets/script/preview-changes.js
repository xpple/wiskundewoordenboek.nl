/**
 * @type {HTMLTextAreaElement}
 */
const changesTextArea = document.getElementById("changes-textarea") ?? document.getElementById("content-textarea");
const changesDiv = document.getElementById("changes-div") ?? document.getElementById("content-div");
const writeButton = document.getElementById("write-button");
const previewButton = document.getElementById("preview-button");

writeButton.addEventListener('click', () => {
    changesDiv.innerHTML = '';
    changesDiv.style.display = 'none';
    changesTextArea.removeAttribute('style');
});

previewButton.addEventListener('click', () => {
    changesDiv.innerHTML = changesTextArea.value;
    changesDiv.removeAttribute('style');
    changesTextArea.style.display = 'none';
    MathJax.typeset();
});
