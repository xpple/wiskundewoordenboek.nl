document.querySelectorAll("h1, h2, h3, h4, h5, h6").forEach(e => {
    if (e.id === "") {
        return;
    }
    const anchor = document.createElement('a');
    anchor.href = `#${e.id}`;
    anchor.innerHTML = "#";
    anchor.classList.add("anchor");
    e.prepend(anchor);
});
