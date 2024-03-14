for (const element of document.getElementsByClassName("render-markdown")) {
    element.innerHTML = marked.parseInline(element.innerHTML);
}
