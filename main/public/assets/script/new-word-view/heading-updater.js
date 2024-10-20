const titleHeading = document.querySelector("#title-container > h1");
/**
 * @type {HTMLAnchorElement}
 */
const titleHeadingAnchor = titleHeading.querySelector("a.anchor")
/**
 * @type {HTMLInputElement}
 */
const titleInput = document.getElementById("title-input");

const updater = () => {
    const value = sanitize(titleInput.value);
    window.history.pushState(value, null, `/woord/${value}/`);
    document.title = value + " creëren | Wiskunde Woordenboek";
    titleHeading.id = value;
    titleHeadingAnchor.href = `#${value}`;
}
titleInput.addEventListener('input', updater);
updater();
