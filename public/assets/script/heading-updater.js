const sanitizePattern = /\p{Mn}|[^a-zA-Z0-9-]/gu;

const titleHeading = document.querySelector("#title-container > h1");
/**
 * @type {HTMLAnchorElement}
 */
const titleHeadingAnchor = titleHeading.querySelector("a.anchor")
/**
 * @type {HTMLInputElement}
 */
const titleInput = document.getElementById("title-input");

titleInput.addEventListener('input', () => {
    const value = sanitize(titleInput.value);
    window.history.pushState(value, null, `/woord/${value}/`);
    document.title = value + " creëren | Wiskunde Woordenboek";
    titleHeading.id = value;
    titleHeadingAnchor.href = `#${value}`;
    titleInput.defaultValue = value;
});

function sanitize(string) {
    return string
        .normalize("NFD")
        .replaceAll(/\s+/g, '-')
        .replaceAll(sanitizePattern, '')
        .toLowerCase();
}
